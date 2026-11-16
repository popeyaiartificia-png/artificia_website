<?php if ($visual === 'website'): ?>
<div class="work-browser">
  <div class="work-browser__bar"><span aria-hidden="true">● ● ●</span><span>orkaytiles.com</span><?= icon('arrow-up-right') ?></div>
  <img src="/assets/img/showcase/orkay-desktop.webp" width="1440" height="960" loading="lazy" decoding="async" alt="Desktop preview of the Orkay Tiles website">
</div>
<?php elseif ($visual === 'ghl'): ?>
<div class="work-ghl" aria-hidden="true">
  <div class="work-ghl__bar"><span><?= brand('gohighlevel') ?>GoHighLevel</span><span>ORKAY TILES</span></div>
  <div class="work-ghl__workspace">
    <aside><span class="work-ghl__active">Overview</span><span>Conversations</span><span>Contacts</span><span>Pipelines</span><span>Automation</span><span class="work-ghl__help">Ongoing support</span></aside>
    <div class="work-ghl__main">
      <span class="work-ui-note">SETUP &amp; SUPPORT</span>
      <p>Connected systems.<br><em>Continued support.</em></p>
      <div class="work-ghl__pipeline"><span>Enquiry<i></i><i></i></span><span>Assigned<i></i><i></i></span><span>Follow-up<i></i><i></i></span></div>
      <div class="work-ghl__services"><span><?= icon('check') ?>Setup</span><span><?= icon('check') ?>Management</span><span><?= icon('check') ?>Support</span></div>
    </div>
  </div>
</div>
<?php elseif ($visual === 'mobile'): ?>
<div class="work-phone">
  <div class="work-phone__camera" aria-hidden="true"></div>
  <img src="/assets/img/showcase/orkay-mobile.webp" width="390" height="844" loading="lazy" decoding="async" alt="Mobile preview of the Orkay Tiles website">
</div>
<?php elseif ($visual === 'conversations'): ?>
<?= render_view('work-service', ['visual' => $visual]) ?>
<?php elseif ($visual === 'support'): ?>
<div class="work-support" aria-hidden="true">
  <span class="work-ui-note">IT SUPPORT &amp; SYSTEMS</span>
  <svg viewBox="0 0 470 240" fill="none" aria-hidden="true" focusable="false">
    <ellipse cx="235" cy="124" rx="210" ry="102" stroke="#7D905D" opacity=".15"/>
    <path d="M316 82h29q15 0 15 15v18M75 170H49q-13 0-13-13v-17" stroke="#768A56" stroke-width="1.5"/>
    <rect x="70" y="35" width="250" height="155" rx="10" fill="#293620" stroke="#7F925F" stroke-width="1.5"/>
    <rect x="81" y="46" width="228" height="131" rx="4" fill="#151D0F"/>
    <path d="M166 190v24h58v-24m-83 25h108" stroke="#7F925F" stroke-width="2" stroke-linecap="round"/>
    <circle cx="195" cy="110" r="50" fill="var(--lime)" opacity=".08"/>
    <g stroke="var(--lime)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
      <path d="M159 123v-18a36 36 0 0 1 72 0v18"/>
      <rect x="151" y="116" width="16" height="30" rx="6"/>
      <rect x="223" y="116" width="16" height="30" rx="6"/>
      <path d="M231 146v5a13 13 0 0 1-13 13h-15"/>
      <path d="M195 164h8" stroke-width="7"/>
    </g>
    <rect x="293" y="111" width="135" height="78" rx="9" fill="#364529" stroke="#829663" stroke-width="1.5"/>
    <circle cx="315" cy="133" r="10" fill="var(--lime)"/>
    <path d="m310 133 3 3 6-7" stroke="#26331B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <text x="334" y="138" fill="#DDE5D3" font-family="Artificia Sans, Arial, sans-serif" font-size="13">Helpdesk</text>
    <path d="M307 156h94m-94 12h68" stroke="#819568" stroke-width="2" stroke-linecap="round"/>
  </svg>
  <div class="work-support__foot"><span>Systems</span><span>Maintenance</span><span>Support</span></div>
</div>
<?php elseif ($visual === 'workflow'): ?>
<div class="work-flow" aria-hidden="true">
  <span class="work-ui-note">LESS COPYING. MORE FLOW.</span>
  <div class="work-flow__nodes"><span><?= icon('file-text') ?><small>Input</small></span><i></i><span class="work-flow__core"><?= icon('flow-arrow') ?><small>Connect</small></span><i></i><span><?= icon('database') ?><small>System</small></span></div>
  <div class="work-flow__foot"><span>Forms</span><span>Sheets</span><span>CRM</span><span>In sync ↗</span></div>
</div>
<?php elseif (in_array($visual, ['knowledge', 'assistants'], true)): ?>
<?= render_view('work-service', ['visual' => $visual]) ?>
<?php else: ?>
<?= render_view('work-service', ['visual' => 'voice']) ?>
<?php endif ?>
