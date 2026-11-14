<?php
/** The free pilot as a pass: workflow-review illustration, offer and facts on a perforated ticket.
 *  cta fields: heading, text, button_label, button_link, details. */
$details = array_values(array_filter($f['details'] ?? [], fn($d) => trim((string) ($d['value'] ?? '')) !== ''));
?>
<section class="sec co-pass" id="pilot">
  <canvas class="topo-bg" aria-hidden="true"></canvas>
  <div class="wrap">
    <div class="co-pass__ticket">
      <div class="co-pass__main">
        <div class="co-pass__content">
          <div class="co-pass__copy">
            <p class="co-pass__kicker"><span class="sol-dot" aria-hidden="true"></span>Start here</p>
            <?= sec_head($f['heading'] ?? '') ?>
<?php if (!empty($f['text'])): ?>
            <p class="co-pass__text"><?= e($f['text']) ?></p>
<?php endif ?>
<?php if (($f['button_label'] ?? '') !== '' && ($f['button_link'] ?? '') !== ''): ?>
            <a class="btn co-pass__btn" href="<?= link_href($f['button_link']) ?>"><?= e($f['button_label']) ?><?= icon('arrow-up-right') ?></a>
<?php endif ?>
          </div>
          <figure class="co-pass__visual" role="img" aria-label="Team documents under a magnifying glass, exploring AI opportunities for productivity and time savings.">
          <svg class="co-pass__art" viewBox="0 0 360 370" fill="none" aria-hidden="true" focusable="false">
            <g stroke="#4e5d00" opacity=".16">
              <circle cx="180" cy="170" r="146" stroke-dasharray="3 8"/>
              <circle cx="180" cy="170" r="112"/>
              <path d="M15 170h24m282 0h24M180 5v24m0 282v24"/>
            </g>
            <g class="co-pass__papers">
              <rect x="57" y="62" width="153" height="189" rx="6" fill="#e2e7d8" stroke="#a9b19c" transform="rotate(-13 133 156)"/>
              <rect x="74" y="48" width="153" height="193" rx="6" fill="#fff" stroke="#abb29f" transform="rotate(-5 150 144)"/>
              <g transform="rotate(5 165 141)">
                <rect x="91" y="43" width="153" height="193" rx="6" fill="#fbfcf8" stroke="#7a856a"/>
                <rect x="107" y="61" width="29" height="29" rx="4" fill="#232719"/>
                <path d="M115 69h9l5 5v9h-14zm9 0v6h5" stroke="var(--lime)" stroke-width="1.3" stroke-linejoin="round"/>
                <path d="M147 68h56m-56 12h37M108 108h116m-116 13h82m-82 14h103" stroke="#abb29f" stroke-width="2.5" stroke-linecap="round"/>
                <rect x="105" y="149" width="119" height="20" rx="2" fill="var(--lime)"/>
                <path d="M112 159h99m-103 27h62m-62 13h80" stroke="#4e5d00" stroke-width="2.5" stroke-linecap="round"/>
                <circle cx="216" cy="207" r="12" fill="#eef1e7" stroke="#abb29f"/>
                <path d="m211 207 3 3 6-7" stroke="#4e5d00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </g>
            </g>
            <g class="co-pass__lens">
              <path d="m267 195 33 38" stroke="#11150f" stroke-width="20" stroke-linecap="round"/>
              <path d="m283 215 12 14" stroke="var(--lime)" stroke-width="4" stroke-linecap="round"/>
              <circle cx="225" cy="148" r="66" fill="#11150f"/>
              <circle cx="225" cy="148" r="56" fill="#232e16" stroke="var(--lime)" stroke-width="1.5"/>
              <path d="M183 132a45 45 0 0 1 30-27" stroke="#f4f6f1" stroke-opacity=".5" stroke-width="3" stroke-linecap="round"/>
              <path d="m225 119 7 21 21 8-21 7-7 22-7-22-21-7 21-8z" fill="var(--lime)"/>
              <path d="m252 115 3 8 8 3-8 3-3 8-3-8-8-3 8-3z" fill="var(--lime)"/>
              <circle cx="225" cy="148" r="77" stroke="#4e5d00" stroke-opacity=".28" stroke-dasharray="2 6"/>
            </g>
            <path class="co-pass__trail" d="M61 213c-39 42-29 82 36 82" stroke="#637046" stroke-width="1.5" stroke-dasharray="4 5"/>
            <path d="m89 289 9 6-9 6" stroke="#637046" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <g class="co-pass__opportunity" transform="rotate(-4 217 293)">
              <rect x="102" y="248" width="231" height="101" rx="8" fill="#11150f"/>
              <path d="M120 275h195" stroke="var(--lime)" stroke-opacity=".2"/>
              <text x="119" y="267" fill="var(--lime)" font-size="16" font-weight="650">AI opportunities</text>
              <g stroke="var(--lime)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="126" cy="295" r="7"/>
                <path d="M126 291v4l3 2"/>
                <path d="m120 327 4-5 4 2 6-9m-5 0h5v5"/>
              </g>
              <text x="143" y="299" fill="#e9eddf" font-size="13">Time savings</text>
              <text x="143" y="326" fill="#e9eddf" font-size="13">Productivity</text>
            </g>
            <g fill="#4e5d00">
              <circle cx="44" cy="108" r="4"/>
              <path d="M304 66v16m-8-8h16M42 253v10m-5-5h10" stroke="#4e5d00" stroke-width="1.5"/>
            </g>
          </svg>
          <figcaption aria-hidden="true">A closer look at your workflow</figcaption>
          </figure>
        </div>
      </div>
<?php if ($details): ?>
      <div class="co-pass__stub">
        <dl class="co-pass__facts">
<?php foreach ($details as $d): ?>
          <div><dt><?= e($d['label'] ?? '') ?></dt><dd><?= e($d['value']) ?></dd></div>
<?php endforeach ?>
        </dl>
        <svg class="co-pass__dial" viewBox="0 0 144 144" fill="none" aria-hidden="true" focusable="false">
          <circle cx="72" cy="72" r="65" stroke="currentColor" stroke-opacity=".45" stroke-dasharray="1 7.5"/>
          <circle cx="72" cy="72" r="55" stroke="currentColor" stroke-opacity=".35"/>
          <path d="M72 10v12m0 100v12M10 72h12m100 0h12" stroke="currentColor" stroke-width="2"/>
          <text x="72" y="79" fill="currentColor" text-anchor="middle" font-size="46" font-weight="650" letter-spacing="-3">60</text>
          <text x="72" y="98" fill="currentColor" text-anchor="middle" font-size="9" font-weight="600" letter-spacing="2">MINUTES</text>
        </svg>
        <span class="co-pass__code" aria-hidden="true"></span>
      </div>
<?php endif ?>
    </div>
  </div>
</section>
