<footer id="footer">
  <div class="container-fluid">
    <div class="row">
      <div id="footer-left" class="col-12 col-sm-6 order-last order-sm-first ">
        <span id="quadratic-link">
          Powered by<br><a href="https://www.quadratic.at/" target="_blank"><img src="/img/quadratic.png" alt="Quadratic Logo"></a>
        </span>
      </div>
      <div id="footer-right" class="col-12 col-sm-6 order-sm-last order-first">
        <ul id="footer-links">
          <li><a href="{{ route('info') }}">{{ __('nav.info') }}</a></li>
          <li><a href="{{ route('impressum') }}">{{ __('nav.impressum') }}</a></li>
        </ul>
      </div>      
    </div>
  </div>
</footer>
