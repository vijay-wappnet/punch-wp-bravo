@php
    use Illuminate\Support\Facades\Vite;
@endphp

<footer class="content-info site-footer" role="contentinfo">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p>&copy; {{ date('Y') }} {{ get_bloginfo('name') }}. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <nav class="footer-navigation" role="navigation" aria-label="{{ __('Footer Menu', 'sage') }}">
                    {{-- {!! wp_nav_menu(['theme_location' => 'footer_navigation', 'menu_class' => 'nav']) !!} --}}
                </nav>
            </div>
        </div>
    </div>
</footer>
{{-- <footer class="content-info">
  @php(dynamic_sidebar('sidebar-footer'))
</footer> --}}
