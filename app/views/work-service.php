<?php
$service = match ($visual) {
    'conversations' => [
        'kind' => 'automation', 'label' => '01 / AI & automation', 'icon' => 'flow-arrow',
        'headline' => 'Less busywork.', 'accent' => 'More possibility.',
        'intro' => 'AI workflows, agents and connected systems. Less copying. More doing.',
        'cta' => 'Explore AI & automation',
    ],
    'knowledge' => [
        'kind' => 'rag', 'label' => '03 / RAG chatbot', 'icon' => 'chat-circle-text',
        'headline' => 'Your knowledge.', 'accent' => 'In conversation.',
        'intro' => 'A chatbot that finds answers in your documents, FAQs and team knowledge.',
        'cta' => 'Explore RAG chatbots',
    ],
    default => [
        'kind' => 'voice', 'label' => '02 / AI voice agents', 'icon' => 'microphone',
        'headline' => 'Your business.', 'accent' => 'With a voice.',
        'intro' => 'Voice agents for enquiries, follow-ups and customer support.',
        'cta' => 'Explore Voice AI',
    ],
};
?>
<div class="work-service work-service--<?= $service['kind'] ?>">
  <div class="work-service__eyebrow"><span><?= e($service['label']) ?></span><?= icon($service['icon']) ?></div>
  <div class="work-service__art" aria-hidden="true">
<?php if ($service['kind'] === 'automation'): ?>
    <svg class="work-service__sculpture work-service__routing" viewBox="0 0 360 190" fill="none" focusable="false">
      <defs>
        <linearGradient id="work-routing" x1="76" y1="30" x2="286" y2="165" gradientUnits="userSpaceOnUse"><stop stop-color="#617F4C"/><stop offset=".5" stop-color="#AED534"/><stop offset="1" stop-color="#759A4D"/></linearGradient>
      </defs>
      <ellipse cx="180" cy="104" rx="143" ry="65" stroke="#D9E9C9" stroke-opacity=".1"/>
      <path d="M36 109h288M180 14v164" stroke="#D9E9C9" stroke-opacity=".08" stroke-dasharray="2 6"/>
      <path class="work-service__route" d="M78 114c0-72 90-103 119-36s88 69 88-1" stroke="#0A170F" stroke-width="27" stroke-linecap="round"/>
      <path class="work-service__route" d="M78 108c0-72 90-103 119-36s88 69 88-1" stroke="url(#work-routing)" stroke-width="20" stroke-linecap="round"/>
      <path d="M80 105c0-67 87-97 114-35" stroke="#E2F4C6" stroke-opacity=".45" stroke-width="1.5" stroke-linecap="round"/>
      <g class="work-service__satellite">
        <rect x="49" y="94" width="58" height="58" rx="17" fill="#2F4432" stroke="#809B68"/>
        <path d="M67 114h22m-22 7h15m-15 7h19" stroke="#C8D9B8" stroke-width="2" stroke-linecap="round"/>
      </g>
      <g transform="translate(181 80) rotate(-12)">
        <rect x="-31" y="-31" width="62" height="62" rx="20" fill="#AED534" stroke="#D9EE9F"/>
        <path d="m1-17 3 12 12 5-12 4-4 13-4-13-12-4 12-5 5-12Z" fill="#1C3021"/>
      </g>
      <rect x="257" y="43" width="56" height="56" rx="17" fill="#2F4432" stroke="#809B68"/>
      <path d="m275 70 7 7 13-15" stroke="#C8D9B8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="121" cy="155" r="4" fill="#AED534"/><path d="M319 138v10m-5-5h10M35 44v10m-5-5h10" stroke="#859B73" stroke-width="1.5"/>
    </svg>
<?php elseif ($service['kind'] === 'voice'): ?>
    <svg class="work-service__sculpture work-service__sound" viewBox="0 0 360 190" fill="none" focusable="false">
      <defs>
        <radialGradient id="work-voice" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(157 61) rotate(57) scale(130)"><stop stop-color="#F0F6D6"/><stop offset=".45" stop-color="#BCD787"/><stop offset=".78" stop-color="#8CA957"/><stop offset="1" stop-color="#5D783D"/></radialGradient>
      </defs>
      <ellipse cx="180" cy="97" rx="127" ry="55" transform="rotate(-23 180 97)" stroke="#5D773C" stroke-opacity=".23"/>
      <circle cx="180" cy="95" r="83" stroke="#6D8649" stroke-opacity=".12" stroke-dasharray="2 5"/>
      <circle cx="180" cy="98" r="68" fill="#658544" fill-opacity=".1"/>
      <circle cx="180" cy="92" r="65" fill="url(#work-voice)" stroke="#8EAB61" stroke-opacity=".6"/>
      <path d="M126 76a57 57 0 0 1 70-36" stroke="#FAFFE7" stroke-opacity=".65" stroke-linecap="round"/>
      <g class="work-service__wave" fill="#29472C">
<?php foreach ([14, 25, 41, 30, 54, 70, 45, 64, 82, 57, 72, 40, 51, 29, 16] as $bar => $height): ?>
        <rect x="<?= 137 + $bar * 6 ?>" y="<?= 92 - $height / 2 ?>" width="3.5" height="<?= $height ?>" rx="1.75" style="--bar:<?= $bar ?>"/>
<?php endforeach ?>
      </g>
      <path d="M66 126c21 23 72 23 128 3s95-46 100-68" stroke="#5D773C" stroke-opacity=".35"/>
      <circle class="work-service__satellite" cx="285" cy="65" r="10" fill="#AED534" stroke="#77934F"/>
      <circle cx="78" cy="121" r="4" fill="#6D8649"/>
      <path d="M305 133v12m-6-6h12M61 43v8m-4-4h8" stroke="#71874F" stroke-width="1.5"/>
    </svg>
<?php else: ?>
    <svg class="work-service__sculpture work-service__knowledge" viewBox="0 0 360 190" fill="none" focusable="false">
      <ellipse cx="180" cy="105" rx="136" ry="61" transform="rotate(-12 180 105)" stroke="#81906C" stroke-opacity=".2"/>
      <path d="M48 127c29 0 34-45 68-45h123" stroke="#829965" stroke-dasharray="3 5"/>
      <g transform="rotate(-16 144 90)">
        <rect x="91" y="26" width="104" height="130" rx="14" fill="#DCE5CD" stroke="#A5B68A"/>
        <path d="M110 50h55m-55 11h38m-38 57h55m-55 11h33" stroke="#A4B490" stroke-width="3" stroke-linecap="round"/>
      </g>
      <g class="work-service__document" transform="rotate(8 184 98)">
        <rect x="136" y="35" width="103" height="130" rx="14" fill="#233C2B" stroke="#496349"/>
        <path d="M154 57h43m-43 10h62" stroke="#839A77" stroke-width="3" stroke-linecap="round"/>
        <rect x="152" y="82" width="70" height="21" rx="4" fill="#AED534"/>
        <path d="M161 92h49" stroke="#42642E" stroke-width="2" stroke-linecap="round"/>
        <path d="M154 117h53m-53 10h36m-36 10h59" stroke="#839A77" stroke-width="3" stroke-linecap="round"/>
      </g>
      <g class="work-service__reply">
        <path d="M231 76h60a16 16 0 0 1 16 16v24a16 16 0 0 1-16 16h-35l-18 13v-13h-7a16 16 0 0 1-16-16V92a16 16 0 0 1 16-16Z" fill="#F5F7EC" stroke="#9FB28A"/>
        <circle cx="245" cy="104" r="4" fill="#648145"/><circle cx="261" cy="104" r="4" fill="#648145"/><circle cx="277" cy="104" r="4" fill="#648145"/>
      </g>
      <circle cx="71" cy="126" r="5" fill="#AED534" stroke="#8CA464"/>
      <path d="M274 34v12m-6-6h12M65 66v8m-4-4h8" stroke="#81956B" stroke-width="1.5"/>
    </svg>
<?php endif ?>
  </div>
  <div class="work-service__copy">
    <p class="work-service__headline"><?= e($service['headline']) ?><br><em><?= e($service['accent']) ?></em></p>
    <p class="work-service__intro"><?= e($service['intro']) ?></p>
  </div>
  <div class="work-service__footer"><span><?= e($service['cta']) ?></span><span class="work-service__arrow"><?= icon('arrow-up-right') ?></span></div>
</div>
