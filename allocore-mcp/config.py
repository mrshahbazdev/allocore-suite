import os
from pathlib import Path
from dotenv import dotenv_values

BASE_DIR = Path(__file__).resolve().parent.parent
ENV_PATH = BASE_DIR / ".env"

env_vars = {}
if ENV_PATH.exists():
    env_vars = dotenv_values(ENV_PATH)

DB_CONNECTION = env_vars.get("DB_CONNECTION", os.getenv("DB_CONNECTION", "mysql"))
DB_HOST = env_vars.get("DB_HOST", os.getenv("DB_HOST", "127.0.0.1"))
DB_PORT = int(env_vars.get("DB_PORT", os.getenv("DB_PORT", 3306)))
DB_DATABASE = env_vars.get("DB_DATABASE", os.getenv("DB_DATABASE", "allocore"))
DB_USERNAME = env_vars.get("DB_USERNAME", os.getenv("DB_USERNAME", "root"))
DB_PASSWORD = env_vars.get("DB_PASSWORD", os.getenv("DB_PASSWORD", ""))

APP_NAME = env_vars.get("APP_NAME", "Allocore Suite")
APP_URL = env_vars.get("APP_URL", "https://allocore.de")
APP_ENV = env_vars.get("APP_ENV", "production")
