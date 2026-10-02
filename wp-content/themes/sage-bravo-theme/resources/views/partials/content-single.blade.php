{{-- The post is built from blocks (they carry their own heading), so the default
     title / date / author header and the comments area are not output here. --}}
<article @php(post_class('h-entry'))>
  <div class="e-content">
    @php(the_content())
  </div>

  @if ($pagination())
    <footer>
      <nav class="page-nav" aria-label="Page">
        {!! $pagination !!}
      </nav>
    </footer>
  @endif
</article>
