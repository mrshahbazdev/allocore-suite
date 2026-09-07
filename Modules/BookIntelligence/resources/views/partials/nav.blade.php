@php($links = [
    [
        'route' => 'bookintelligence.dashboard',
        'label' => __('Dashboard'),
        'active' => 'bookintelligence.dashboard',
    ],
    [
        'route' => 'bookintelligence.books.index',
        'label' => __('Book Library'),
        'active' => 'bookintelligence.books.*',
    ],
    [
        'route' => 'bookintelligence.questions.index',
        'label' => __('Question Engine & FAQs'),
        'active' => 'bookintelligence.questions.*',
    ],
    [
        'route' => 'bookintelligence.search.index',
        'label' => __('Knowledge Search'),
        'active' => 'bookintelligence.search.*',
    ],
    [
        'route' => 'bookintelligence.gaps.index',
        'label' => __('Knowledge Gaps'),
        'active' => 'bookintelligence.gaps.*',
    ],
    [
        'route' => 'bookintelligence.setup.index',
        'label' => __('Library Setup'),
        'active' => 'bookintelligence.setup.*',
    ],
    [
        'route' => 'bookintelligence.guide',
        'label' => __('How It Works'),
        'active' => 'bookintelligence.guide',
    ],
])

<div data-no-navigate>
    @include('partials.module-nav', ['layout' => $layout ?? 'horizontal'])
</div>
