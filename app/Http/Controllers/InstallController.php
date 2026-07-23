<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class InstallController extends Controller
{
    protected const MIN_PHP_VERSION = '8.3';

    public function index()
    {
        if (config('app.installed')) {
            return redirect('/');
        }

        $checks = [
            'PHP >= '.self::MIN_PHP_VERSION => version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>='),
            'OpenSSL extension' => extension_loaded('openssl'),
            'PDO extension' => extension_loaded('pdo'),
            'Mbstring extension' => extension_loaded('mbstring'),
            'Tokenizer extension' => extension_loaded('tokenizer'),
            'XML extension' => extension_loaded('xml'),
            'Ctype extension' => extension_loaded('ctype'),
            'JSON extension' => extension_loaded('json'),
            '.env file writable' => is_writable(base_path('.env')),
            'Storage directory writable' => is_writable(storage_path()),
            'Cache directory writable' => is_writable(base_path('bootstrap/cache')),
        ];

        return view('install.index', ['passed' => ! in_array(false, $checks, true), 'checks' => $checks]);
    }

    public function database()
    {
        if (config('app.installed')) {
            return redirect('/');
        }

        return view('install.database');
    }

    public function storeDatabase(Request $request)
    {
        if (config('app.installed')) {
            return redirect('/');
        }

        $validated = $request->validate([
            'db_driver' => 'required|in:sqlite,mysql',
            'db_host' => 'nullable|string|max:255',
            'db_port' => 'nullable|integer',
            'db_database' => 'required|string|max:255',
            'db_username' => 'nullable|string|max:255',
            'db_password' => 'nullable|string|max:255',
            'db_prefix' => 'nullable|string|max:50',
        ]);

        $driver = $validated['db_driver'];

        if ($driver === 'sqlite') {
            $path = $validated['db_database'];
            if (! Str::startsWith($path, ['/', '\\', DIRECTORY_SEPARATOR])) {
                $path = database_path($path);
            }

            $directory = dirname($path);
            if (! is_dir($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            if (! file_exists($path) && ! touch($path)) {
                return back()->with('error', __('Could not create the SQLite database file. Please check directory permissions.'));
            }

            $validated['db_database'] = $path;
        }

        try {
            $this->testConnection($validated);
        } catch (\Throwable $e) {
            return back()->with('error', __('Database connection failed: ').$e->getMessage())->withInput();
        }

        session(['install.database' => $validated]);

        return redirect()->route('install.admin');
    }

    public function admin()
    {
        if (config('app.installed')) {
            return redirect('/');
        }

        if (! session()->has('install.database')) {
            return redirect()->route('install.database');
        }

        return view('install.admin');
    }

    public function run(Request $request)
    {
        if (config('app.installed')) {
            return redirect('/');
        }

        $database = session('install.database');

        if (! $database) {
            return redirect()->route('install.index');
        }

        $admin = $request->validate([
            'site_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->applyDatabaseConfig($database);

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return redirect()->route('install.database')->with('error', __('Database connection lost: ').$e->getMessage());
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--class' => 'Database\Seeders\CoreSeeder', '--force' => true]);

            $user = User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'email_verified_at' => now(),
                ]
            );

            Role::findOrCreate('admin');
            $user->assignRole('admin');

            if (! $user->current_team_id) {
                $team = Team::create([
                    'name' => $admin['name'].'\'s Team',
                    'owner_id' => $user->id,
                ]);
                $team->members()->attach($user->id, ['role' => 'owner']);
                $user->update(['current_team_id' => $team->id]);
            }

            $this->writeEnv($database, $admin);

            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            return redirect()->route('install.database')->with('error', __('Installation failed: ').$e->getMessage());
        }

        session()->forget(['install.database', 'install.admin']);

        return redirect()->route('login');
    }

    protected function testConnection(array $database): void
    {
        $config = $this->connectionConfig($database);
        $connection = DB::build($config);
        $connection->getPdo();
        $connection->disconnect();
    }

    protected function applyDatabaseConfig(array $database): void
    {
        $config = $this->connectionConfig($database);

        Config::set('database.default', $database['db_driver']);
        Config::set('database.connections.'.$database['db_driver'], $config);

        DB::purge($database['db_driver']);
    }

    protected function connectionConfig(array $database): array
    {
        $driver = $database['db_driver'];

        if ($driver === 'sqlite') {
            return [
                'driver' => 'sqlite',
                'url' => null,
                'database' => $database['db_database'],
                'prefix' => $database['db_prefix'] ?? '',
                'foreign_key_constraints' => true,
            ];
        }

        return [
            'driver' => 'mysql',
            'host' => $database['db_host'] ?? '127.0.0.1',
            'port' => $database['db_port'] ?? 3306,
            'database' => $database['db_database'],
            'username' => $database['db_username'] ?? 'root',
            'password' => $database['db_password'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => $database['db_prefix'] ?? '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ];
    }

    protected function writeEnv(array $database, array $admin): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $examplePath = base_path('.env.example');
            if (file_exists($examplePath)) {
                File::copy($examplePath, $envPath);
            } else {
                File::put($envPath, '');
            }
        }

        $contents = File::get($envPath);

        $updates = [
            'APP_NAME' => '"'.$admin['site_name'].'"',
            'APP_INSTALLED' => 'true',
        ];

        if (empty(env('APP_KEY'))) {
            $updates['APP_KEY'] = 'base64:'.base64_encode(random_bytes(32));
        }

        if ($database['db_driver'] === 'sqlite') {
            $updates['DB_CONNECTION'] = 'sqlite';
            $updates['DB_DATABASE'] = $database['db_database'];
            $updates['DB_HOST'] = '';
            $updates['DB_PORT'] = '';
            $updates['DB_USERNAME'] = '';
            $updates['DB_PASSWORD'] = '';
        } else {
            $updates['DB_CONNECTION'] = 'mysql';
            $updates['DB_HOST'] = $database['db_host'] ?? '127.0.0.1';
            $updates['DB_PORT'] = $database['db_port'] ?? 3306;
            $updates['DB_DATABASE'] = $database['db_database'];
            $updates['DB_USERNAME'] = $database['db_username'] ?? 'root';
            $updates['DB_PASSWORD'] = $database['db_password'] ?? '';
        }

        foreach ($updates as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $line = "{$key}={$value}";

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $line, $contents);
            } else {
                $contents .= "\n{$line}";
            }
        }

        File::put($envPath, $contents);
    }
}
