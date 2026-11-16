<?php if ($visual === 'conversations'): ?>
<div class="work-service work-service--automation">
  <div class="work-service__eyebrow"><span>01 / AI &amp; automation</span><?= icon('flow-arrow') ?></div>
  <p class="work-service__headline">Less busywork.<br><em>More possibility.</em></p>
  <p class="work-service__intro">Connect your tools. Give your team<br>room to do better work.</p>
  <div class="work-service__workflow" aria-hidden="true">
    <span class="work-service__workflow-label">A MORE CONNECTED WORKDAY</span>
    <div class="work-service__workflow-nodes">
      <span><?= icon('tray') ?><small>Enquiry</small></span>
      <i></i><span class="work-service__workflow-core"><?= icon('flow-arrow') ?><small>AI workflow</small></span>
      <i></i><span><?= icon('database') ?><small>Your CRM</small></span>
    </div>
    <span class="work-service__workflow-note"><?= icon('check') ?> Right information. Right place.</span>
  </div>
  <ul class="work-service__capabilities">
    <li><span class="work-service__number">01</span><span><strong>AI automation</strong><small>Less copying. More connected systems.</small></span><?= icon('arrow-up-right') ?></li>
    <li><span class="work-service__number">02</span><span><strong>Agentic AI</strong><small>Your knowledge, ready to help your team.</small></span><?= icon('arrow-up-right') ?></li>
    <li><span class="work-service__number">03</span><span><strong>Marketing automation</strong><small>Keep enquiries and follow-ups in sync.</small></span><?= icon('arrow-up-right') ?></li>
  </ul>
  <div class="work-service__footer"><span>Explore AI &amp; automation</span><span class="work-service__arrow"><?= icon('arrow-up-right') ?></span></div>
</div>
<?php elseif ($visual === 'knowledge'): ?>
<div class="work-service work-service--digital">
  <div class="work-service__eyebrow"><span>03 / Digital solutions</span><span class="work-service__asterisk" aria-hidden="true">&#10035;</span></div>
  <div class="work-service__digital-head">
    <p class="work-service__headline">Built for<br><em>what&rsquo;s next.</em></p>
    <div class="work-service__launch" aria-hidden="true">
      <div class="work-service__launch-back"></div>
      <div class="work-service__launch-window"><span><i></i><i></i><i></i></span><div><b></b><b></b><b></b><b></b></div></div>
      <span class="work-service__launch-check"><?= icon('check') ?></span>
    </div>
  </div>
  <div class="work-service__delivery">
    <div><?= icon('globe') ?><span><strong>Software &amp; apps</strong><small>Built around your business</small></span></div>
    <div><?= icon('database') ?><span><strong>Cloud &amp; private AI</strong><small>Your next idea, deployed</small></span></div>
    <div><?= icon('magnifying-glass') ?><span><strong>SEO / AEO / GEO</strong><small>Be found in search &amp; AI</small></span></div>
    <div><?= icon('graduation-cap') ?><span><strong>Corporate AI training</strong><small>Help your people put AI to use</small></span></div>
  </div>
  <div class="work-service__footer"><span>Explore digital solutions</span><span class="work-service__arrow"><?= icon('arrow-up-right') ?></span></div>
</div>
<?php else: ?>
<div class="work-service work-service--voice">
  <div class="work-service__eyebrow"><span>02 / AI voice agents</span><?= icon('microphone') ?></div>
  <div class="work-service__voice-body">
    <div>
      <p class="work-service__headline">Your business.<br><em>With a voice.</em></p>
      <p class="work-service__intro">Natural conversations.<br>Built around your customers.</p>
    </div>
    <div class="work-service__voice-orb" aria-hidden="true">
      <div class="work-service__wave"><?php for ($bar = 0; $bar < 19; $bar++): ?><i style="--wave:<?= [14, 24, 40, 56, 34, 72, 92, 60, 82, 100, 70, 88, 54, 76, 42, 60, 32, 22, 12][$bar] ?>%;--bar:<?= $bar ?>"></i><?php endfor ?></div>
      <span>LISTEN. UNDERSTAND. ASSIST.</span>
    </div>
  </div>
  <div class="work-service__footer"><span class="work-service__voice-uses">Enquiries <i></i> Follow-ups <i></i> Support</span><span class="work-service__arrow"><?= icon('arrow-up-right') ?></span></div>
</div>
<?php endif ?>
