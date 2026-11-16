<li class="workscape__brands">
  <div class="workscape__caption"><span>Brands we&rsquo;ve worked with</span></div>
  <ul class="workscape__picture work-brands">
<?php foreach (['volkswagen' => 'Volkswagen', 'fashion-code' => 'Fashion Code', 'royal-caribbean' => 'Royal Caribbean International', 'national-geographic' => 'National Geographic Channel', 'godrej-properties' => 'Godrej Properties', 'tata' => 'Tata', 'holland-america' => 'Holland America Line', 'vasan-eye-care' => 'Vasan Eye Care Hospital', 'aircel' => 'Aircel', 'brg-group' => 'BRG Group Sargam House', 'laxcon' => 'Laxcon', 'britannia' => 'Britannia'] as $logo => $brand): ?>
    <li><img src="/assets/img/brands/<?= $logo ?>.webp" alt="<?= $brand ?>" loading="lazy" decoding="async"></li>
<?php endforeach ?>
  </ul>
</li>
