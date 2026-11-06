<?php
/** Illustrative course projects. All scenes are complete without animation. */
if (!in_array($visual ?? '', ['website', 'content', 'assistant', 'automation'], true)) return;
$projectPart = 0;
?>
<svg class="project-visual project-visual--<?= e($visual) ?>" viewBox="0 0 600 340" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" style="font-family:var(--sans,Arial,sans-serif)">
<?php if ($visual === 'website'): ?>
  <path d="M78 20H551V287H78Z" fill="none" stroke="#a1b681" opacity=".2"/>
  <path d="M61 41H548V306H61Z" fill="#0b1408" opacity=".25"/>
  <g class="project-visual__reveal project-visual__browser" style="--project-part:<?= ++$projectPart ?>">
    <path d="M52 31H539V296H52Z" fill="#e9eedf" stroke="#b8c5a4"/>
    <path d="M52 31H539V60H52Z" fill="#35452a"/>
    <g fill="#a6b78d"><circle cx="67" cy="45" r="2"/><circle cx="75" cy="45" r="2"/><circle cx="83" cy="45" r="2"/></g>
    <path d="M184 38H409V53H184Z" fill="#29391f"/>
    <text x="296" y="49" fill="#c5d1b5" font-size="9" text-anchor="middle" letter-spacing=".4">your-project.example</text>
    <text x="81" y="87" fill="#293c20" font-size="11" font-weight="650" letter-spacing="1.5">YOUR NEXT IDEA</text>
    <path d="M393 82H421M439 82H469M487 82H510" stroke="#8a9a76" stroke-width="2"/>
  </g>
  <g class="project-visual__reveal project-visual__page" style="--project-part:<?= ++$projectPart ?>">
    <text x="81" y="133" fill="#1b2c14" font-size="29" font-weight="650" letter-spacing="-1.1">A small idea.</text>
    <text x="81" y="166" fill="#1b2c14" font-size="29" font-weight="650" letter-spacing="-1.1">A real website.</text>
    <text x="82" y="192" fill="#61714e" font-size="12">Built with AI. Made for people.</text>
    <path d="M82 214H239V247H82Z" fill="#d2ff00"/>
    <text x="96" y="234" fill="#243517" font-size="12" font-weight="600">Explore the project</text>
    <path d="M216 226H226V236M216 236 226 226" fill="none" stroke="#243517" stroke-width="1.3"/>
    <path d="M82 268H131M146 268H205M220 268H270" stroke="#9eaf88" stroke-width="3"/>
  </g>
  <g class="project-visual__reveal project-visual__artwork" style="--project-part:<?= ++$projectPart ?>">
    <path d="M327 105H510V252H327Z" fill="#2b3a21"/>
    <path d="M327 214 510 152V252H327Z" fill="#344b25"/>
    <path d="M349 214V184H379V156H409V128H439V214Z" fill="#d2ff00"/>
    <path d="M379 214V184H409V156H439V128H463V214Z" fill="#9fbe47"/>
    <path d="M349 214H483M349 226H483M349 238H483" stroke="#bad38b" stroke-width=".65" opacity=".3"/>
    <circle cx="468" cy="128" r="13" fill="#e7edd8"/>
    <path d="M448 140 475 114" stroke="#2b3a21" stroke-width="1"/>
  </g>
  <g class="project-visual__reveal project-visual__status" style="--project-part:<?= ++$projectPart ?>">
    <path d="M390 277H519V305H390Z" fill="#202f17" stroke="#77935a"/>
    <path class="project-visual__check" d="m402 291 4 4 7-9" pathLength="1" fill="none" stroke="#d2ff00" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    <text x="423" y="295" fill="#dfe8d0" font-size="9" letter-spacing=".4">READY TO PUBLISH</text>
  </g>
  <g class="project-visual__cursor" transform="translate(229 239)">
    <path d="M0 0 0 24 6 18 10 28 15 26 11 16 20 16Z" fill="#f2f5e9" stroke="#26391b" stroke-width="1.3" stroke-linejoin="round"/>
  </g>
<?php elseif ($visual === 'content'): ?>
  <g class="project-visual__reveal project-visual__deck" style="--project-part:<?= ++$projectPart ?>">
    <path d="M172 43H530V257H172Z" fill="#0b1408" opacity=".25"/>
    <path d="M164 35H522V249H164Z" fill="#e9eedf" stroke="#b7c4a0"/>
    <path d="M164 35H522V60H164Z" fill="#cdd7bd"/>
    <text x="181" y="52" fill="#4d6440" font-size="9" letter-spacing="1">PRESENTATION / YOUR NEXT IDEA</text>
    <text x="187" y="102" fill="#21331a" font-size="26" font-weight="650" letter-spacing="-.8">Make the idea clear.</text>
    <text x="188" y="124" fill="#62754f" font-size="12">A story worth sharing.</text>
    <path d="M189 144H487" stroke="#a7b791"/>
    <path d="M190 163H278V215H190Z" fill="#d2ff00"/>
    <path d="M293 163H381V215H293Z" fill="#b5c697"/>
    <path d="M396 163H484V215H396Z" fill="#344b28"/>
    <circle cx="234" cy="189" r="15" fill="#314624"/>
    <path d="M318 203V175H328V203M337 203V183H347V203M356 203V168H366V203" fill="#425d31"/>
    <path d="M416 180H466M416 189H455M416 198H461" stroke="#d8e4c5" stroke-width="3"/>
  </g>
  <g class="project-visual__reveal project-visual__poster" style="--project-part:<?= ++$projectPart ?>" transform="rotate(-7 146 205)">
    <path d="M67 111H243V301H67Z" fill="#0b1408" opacity=".3" transform="translate(5 6)"/>
    <path d="M67 111H243V301H67Z" fill="#26391c" stroke="#748e54"/>
    <text x="83" y="135" fill="#b2c497" font-size="9" letter-spacing="1.2">CAMPAIGN VISUAL</text>
    <circle cx="212" cy="257" r="27" fill="#d2ff00"/>
    <path d="M185 257H239M212 230V284" stroke="#314a1c" stroke-width="1"/>
    <text x="84" y="196" fill="#eff3e6" font-size="27" font-weight="650" letter-spacing="-1">Make your</text>
    <text x="84" y="225" fill="#eff3e6" font-size="27" font-weight="650" letter-spacing="-1">next idea</text>
    <text x="84" y="254" fill="#d2ff00" font-size="27" font-weight="650" letter-spacing="-1">visible.</text>
    <path d="M85 281H147" stroke="#9caf83" stroke-width="2"/>
  </g>
  <g class="project-visual__reveal project-visual__video" style="--project-part:<?= ++$projectPart ?>">
    <path d="M326 182H538V309H326Z" fill="#0b1408" opacity=".3" transform="translate(5 5)"/>
    <path d="M326 182H538V309H326Z" fill="#192814" stroke="#7e9a5e"/>
    <text x="342" y="204" fill="#cad8b6" font-size="9" letter-spacing="1">VIDEO PREVIEW</text>
    <path d="M327 273 386 230 438 254 482 212 537 240V290H327Z" fill="#526e32"/>
    <path d="M327 283 405 253 457 277 537 244V290H327Z" fill="#87a641"/>
    <circle cx="434" cy="244" r="20" fill="#d2ff00"/>
    <path d="m429 235 14 9-14 9Z" fill="#2a3e1b"/>
    <path d="M343 298H518" stroke="#758c5c" stroke-width="2"/>
    <path d="M343 298H407" stroke="#d2ff00" stroke-width="2"/>
    <circle cx="407" cy="298" r="3" fill="#d2ff00"/>
  </g>
<?php elseif ($visual === 'assistant'): ?>
  <g class="project-visual__reveal project-visual__source" style="--project-part:<?= ++$projectPart ?>">
    <path d="M85 80H202V282H85Z" fill="#78915b" opacity=".35" transform="rotate(4 143 181)"/>
    <g transform="rotate(-5 124 168)">
      <path d="M61 58H170L190 78V267H61Z" fill="#e8eedc" stroke="#b9c7a3"/>
      <path d="M170 58V78H190" fill="#c1ceac" stroke="#a0b48a"/>
      <path d="M78 82H103V109H78Z" fill="none" stroke="#6d8552" stroke-width="1.4"/>
      <text x="78" y="134" fill="#314724" font-size="11" font-weight="600" letter-spacing=".6">PROJECT BRIEF</text>
      <path d="M78 147H169" stroke="#9cad88"/>
      <text x="79" y="171" fill="#60764c" font-size="12">Goals</text>
      <text x="79" y="193" fill="#60764c" font-size="12">Audience</text>
      <text x="79" y="215" fill="#60764c" font-size="12">Key messages</text>
      <path d="M79 236H147M79 243H132" stroke="#b0bf9c" stroke-width="2"/>
    </g>
  </g>
  <path class="project-visual__pulse" d="M193 173H211Q225 173 225 159V128H242" pathLength="1" fill="none" stroke="#d2ff00" stroke-width="1.8" stroke-linecap="round"/>
  <g class="project-visual__reveal project-visual__chat" style="--project-part:<?= ++$projectPart ?>">
    <path d="M235 42H545V300H235Z" fill="#0c1609" opacity=".3" transform="translate(5 6)"/>
    <path d="M235 42H545V300H235Z" fill="#24351b" stroke="#708b52"/>
    <path d="M235 42H545V82H235Z" fill="#2f4423"/>
    <path d="M257 52c1.1 5 2.8 6.7 8 8-5.2 1.2-6.9 2.9-8 8-1.2-5.1-2.9-6.8-8-8 5.1-1.3 6.8-3 8-8Z" fill="#d2ff00"/>
    <text x="276" y="65" fill="#e9eedf" font-size="13" font-weight="550">Your document assistant</text>
    <path class="project-visual__check" d="m253 96 3 3 6-7" pathLength="1" fill="none" stroke="#d2ff00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <text x="269" y="99" fill="#a9be91" font-size="10">Source connected</text>
    <path d="M289 115H523V155H289Z" fill="#e7eddc"/>
    <text x="305" y="140" fill="#263b1c" font-size="13">Summarise this brief.</text>
    <g class="project-visual__answer">
      <path d="M253 172H523V246H253Z" fill="#354c28"/>
      <text x="269" y="195" fill="#eef3e4" font-size="13" font-weight="550">Here are the key points.</text>
      <text x="269" y="215" fill="#c1d0ae" font-size="11">Goals · audience · next steps</text>
      <path d="M269 226H367V238H269Z" fill="#293d1f"/>
      <text x="275" y="235" fill="#c5d7aa" font-size="9">Project brief.pdf</text>
    </g>
    <path d="M253 263H523V287H253Z" fill="#1b2b14" stroke="#506b3c"/>
    <text x="265" y="279" fill="#8da779" font-size="11">Ask a question about your files</text>
    <path d="M500 280V271M496 275 500 271 504 275" fill="none" stroke="#d2ff00" stroke-width="1.3"/>
  </g>
<?php elseif ($visual === 'automation'): ?>
  <path class="project-visual__pulse" d="M190 101H228M371 101H409M480 138V207" pathLength="1" fill="none" stroke="#a8c979" stroke-width="2"/>
  <path d="m221 97 7 4-7 4M402 97 409 101 402 105M476 200 480 207 484 200" fill="none" stroke="#d2ff00" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
  <g class="project-visual__reveal project-visual__form" style="--project-part:<?= ++$projectPart ?>">
    <path d="M48 62H190V138H48Z" fill="#e8eedc" stroke="#becbac"/>
    <path d="M63 75H80V94H63Z" fill="none" stroke="#627d48" stroke-width="1.4"/>
    <path d="M67 80H77M67 84H77M67 88H74" stroke="#627d48" stroke-width="1"/>
    <text x="62" y="113" fill="#283d1d" font-size="14" font-weight="600">Form received</text>
    <text x="62" y="128" fill="#6e8159" font-size="10">New enquiry</text>
  </g>
  <g class="project-visual__reveal project-visual__ai-step" style="--project-part:<?= ++$projectPart ?>">
    <path d="M228 62H371V138H228Z" fill="#314924" stroke="#829d5d"/>
    <path d="M248 72c1.1 5.1 2.8 6.8 8 8-5.2 1.2-6.9 2.8-8 8-1.2-5.2-2.9-6.8-8-8 5.1-1.2 6.8-2.9 8-8Z" fill="#d2ff00"/>
    <text x="242" y="113" fill="#f0f4e8" font-size="14" font-weight="600">Sort &amp; draft</text>
    <text x="242" y="128" fill="#b0c498" font-size="10">AI step</text>
  </g>
  <g class="project-visual__reveal project-visual__sheet" style="--project-part:<?= ++$projectPart ?>">
    <path d="M409 62H552V138H409Z" fill="#e8eedc" stroke="#becbac"/>
    <text x="423" y="85" fill="#283d1d" font-size="14" font-weight="600">Update sheet</text>
    <path d="M424 98H537V124H424ZM424 107H537M424 116H537M460 98V124M498 98V124" fill="none" stroke="#91a77b" stroke-width=".85"/>
    <path d="M429 102H449M465 102H489M503 102H526" stroke="#617c45" stroke-width="2"/>
  </g>
  <g class="project-visual__reveal project-visual__log" style="--project-part:<?= ++$projectPart ?>">
    <path d="M48 207H335V280H48Z" fill="#24371a" stroke="#536e3b"/>
    <text x="63" y="226" fill="#9ab480" font-size="9" letter-spacing="1">WORKFLOW PREVIEW</text>
    <path class="project-visual__check" d="m64 242 3 3 6-7" pathLength="1" fill="none" stroke="#d2ff00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <text x="83" y="247" fill="#dbe7cc" font-size="11">Form received</text>
    <path class="project-visual__check" d="m64 261 3 3 6-7" pathLength="1" fill="none" stroke="#d2ff00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <text x="83" y="266" fill="#dbe7cc" font-size="11">Draft created</text>
    <text x="317" y="266" fill="#8aa773" font-size="9" text-anchor="end">Ready to review</text>
  </g>
  <g class="project-visual__reveal project-visual__followup" style="--project-part:<?= ++$projectPart ?>">
    <path d="M409 207H552V280H409Z" fill="#d2ff00"/>
    <path d="M424 221H444V235H424ZM424 221 434 229 444 221" fill="none" stroke="#496521" stroke-width="1.3" stroke-linejoin="round"/>
    <text x="423" y="254" fill="#263b17" font-size="14" font-weight="600">Draft follow-up</text>
    <text x="423" y="270" fill="#4a6429" font-size="10">Your next step, ready.</text>
  </g>
<?php endif ?>
</svg>
