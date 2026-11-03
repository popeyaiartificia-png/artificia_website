<svg class="solution__art" viewBox="0 0 600 350" aria-hidden="true" focusable="false">
  <ellipse class="solution__orbit" cx="300" cy="180" rx="245" ry="135"/>
  <ellipse class="solution__orbit" cx="300" cy="180" rx="195" ry="104"/>
<?php switch ($visual): case 'knowledge': case 'agentic': ?>
  <g class="solution__object">
    <path class="solution__wire" d="M138 131h62q25 0 25 25v36h55m79 0h55q20 0 20-20v-41h35M337 225v49H170v-42"/>
    <path class="solution__pulse" pathLength="1" d="M138 131h62q25 0 25 25v36h55m79 0h55q20 0 20-20v-41h35"/>
    <rect class="solution__panel" x="62" y="84" width="138" height="96" rx="9"/>
    <path class="solution__wire" d="M81 106h44m-44 17h99m-99 12h99m-99 12h67"/>
    <text class="solution__ui-small" x="81" y="166">YOUR KNOWLEDGE</text>
    <rect class="solution__accent" x="257" y="130" width="105" height="105" rx="23"/>
    <path class="solution__ink" d="m310 148 10 24 24 10-24 10-10 25-10-25-24-10 24-10z"/>
    <rect class="solution__panel" x="416" y="90" width="130" height="87" rx="9"/>
    <circle class="solution__accent" cx="438" cy="113" r="5"/>
    <text class="solution__ui-text" x="453" y="117">Tools</text>
    <path class="solution__wire" d="M434 136h94m-94 12h67m-67 12h41"/>
    <rect class="solution__panel" x="97" y="212" width="123" height="44" rx="8"/>
    <path class="solution__wire" d="m112 232 6 6 10-12"/>
    <text class="solution__ui-text" x="138" y="239">Human review</text>
    <rect class="solution__panel" x="392" y="231" width="150" height="53" rx="9"/>
    <text class="solution__ui-small" x="411" y="252">REASON. ACT. REFINE.</text>
    <path class="solution__wire" d="M411 267h47m9 0h22m9 0h22"/>
  </g>
<?php break; case 'software': ?>
  <g class="solution__object">
    <rect class="solution__panel" x="102" y="52" width="338" height="214" rx="10"/>
    <rect class="solution__panel" x="73" y="77" width="354" height="222" rx="10"/>
    <path class="solution__wire" d="M73 107h354M89 92h3m8 0h3m8 0h3M147 108v191"/>
    <path class="solution__wire" d="M89 131h39m-39 18h28m-28 18h31m-31 18h22m-22 85h27"/>
    <text class="solution__ui-text" x="169" y="135">Your workspace</text>
    <rect class="solution__tile" x="167" y="152" width="109" height="43" rx="5"/>
    <rect class="solution__tile" x="286" y="152" width="120" height="43" rx="5"/>
    <path class="solution__wire" d="M180 165h47m-47 15h76m44-15h47m-47 15h86"/>
    <path class="solution__wire" d="M167 276h239M181 260v-22m30 22v-35m30 35v-20m30 20v-44m30 44v-32m30 32v-58m30 58v-42m30 42v-67"/>
    <rect class="solution__accent" x="321" y="202" width="19" height="58" rx="3"/>
    <rect class="solution__panel" x="396" y="167" width="89" height="147" rx="13"/>
    <path class="solution__wire" d="M426 179h30m-26 124h22"/>
    <rect class="solution__accent" x="407" y="191" width="67" height="57" rx="5"/>
    <path class="solution__ink" d="m426 219 10 9 19-22"/>
    <path class="solution__wire" d="M410 264h58m-58 12h58m-58 12h33"/>
  </g>
<?php break; case 'marketing': ?>
  <g class="solution__object">
    <path class="solution__wire" d="M147 107h101q22 0 22 22v33h38m-38 40v47q0 20 22 20h112M330 182h91V107h41M421 182v34h41"/>
    <path class="solution__pulse" pathLength="1" d="M147 107h101q22 0 22 22v33h38M330 182h91V107h41"/>
    <rect class="solution__panel" x="57" y="77" width="131" height="74" rx="9"/>
    <path class="solution__wire" d="M76 95h31v23H76zM76 95l15 12 16-12"/>
    <text class="solution__ui-text" x="76" y="136">New enquiry</text>
    <rect class="solution__accent" x="226" y="143" width="112" height="89" rx="15"/>
    <text class="solution__label solution__label--ink" x="282" y="182" text-anchor="middle">CRM</text>
    <path class="solution__ink" d="M251 205h62m-62 8h42"/>
    <rect class="solution__panel" x="433" y="73" width="120" height="75" rx="9"/>
    <text class="solution__ui-text" x="449" y="100">Follow-up</text>
    <path class="solution__wire" d="M449 115h83m-83 12h54"/>
    <rect class="solution__panel" x="433" y="188" width="120" height="69" rx="9"/>
    <text class="solution__ui-text" x="449" y="213">Conversation</text>
    <path class="solution__wire" d="M449 230h83m-83 12h54"/>
    <rect class="solution__panel" x="93" y="217" width="112" height="58" rx="9"/>
    <text class="solution__ui-small" x="109" y="240">ONE CUSTOMER.</text>
    <text class="solution__ui-small" x="109" y="257">ONE JOURNEY.</text>
    <circle class="solution__accent" cx="405" cy="269" r="7"/>
  </g>
<?php break; case 'search': ?>
  <g class="solution__object">
    <rect class="solution__panel" x="85" y="60" width="353" height="233" rx="11"/>
    <path class="solution__wire" d="M85 91h353M104 75h3m8 0h3m8 0h3"/>
    <rect class="solution__accent" x="108" y="111" width="307" height="39" rx="7"/>
    <text class="solution__ui-ink" x="124" y="135">Search. Ask. Discover.</text>
    <circle class="solution__ink" cx="388" cy="129" r="6"/>
    <path class="solution__ink" d="m393 134 5 5"/>
    <text class="solution__ui-small" x="109" y="177">SEARCH + AI ANSWERS</text>
    <path class="solution__wire" d="M109 193h153m-153 12h197m-197 12h183M109 247h110m-110 12h166m-166 12h137"/>
    <rect class="solution__panel" x="335" y="177" width="184" height="124" rx="10"/>
    <path class="solution__accent" d="m358 190 4 11 11 4-11 4-4 11-4-11-11-4 11-4z"/>
    <text class="solution__ui-text" x="382" y="209">Answer-ready</text>
    <path class="solution__wire" d="M353 230h146m-146 13h146m-146 13h109"/>
    <rect class="solution__tile" x="353" y="269" width="67" height="17" rx="4"/>
    <text class="solution__ui-small" x="363" y="281">CONTENT</text>
    <rect class="solution__tile" x="429" y="269" width="67" height="17" rx="4"/>
    <text class="solution__ui-small" x="439" y="281">CONTEXT</text>
  </g>
<?php break; case 'cloud': ?>
  <g class="solution__object">
    <path class="solution__wire" d="M300 165v66H152v20m148-20h148v20"/>
    <path class="solution__pulse" pathLength="1" d="M300 165v66H152v20m148-20h148v20"/>
    <path class="solution__panel" d="M207 170h186c33 0 45-43 15-57-1-42-56-60-82-24-30-38-87-25-96 13-40-13-74 41-43 61z"/>
    <text class="solution__ui-small" x="300" y="133" text-anchor="middle">YOUR ENVIRONMENT</text>
    <rect class="solution__accent" x="266" y="149" width="68" height="68" rx="15"/>
    <path class="solution__ink" d="M287 179v-9a13 13 0 0 1 26 0v9M283 179h34v25h-34zM300 188v7"/>
    <rect class="solution__panel" x="74" y="248" width="157" height="55" rx="8"/>
    <text class="solution__ui-text" x="91" y="270">Cloud</text>
    <path class="solution__wire" d="M91 286h86"/>
    <circle class="solution__accent" cx="209" cy="276" r="5"/>
    <rect class="solution__panel" x="370" y="248" width="157" height="55" rx="8"/>
    <text class="solution__ui-text" x="387" y="270">Private AI</text>
    <path class="solution__wire" d="M387 286h86"/>
    <circle class="solution__accent" cx="505" cy="276" r="5"/>
    <path class="solution__wire" d="M110 156h31m-15-15v30M456 86h22m-11-11v22"/>
  </g>
<?php break; default: ?>
  <g class="solution__object">
    <path class="solution__wire" d="M168 133h56q22 0 22 22v28h31m62 0h42q22 0 22-22v-28h34M301 216v45H165"/>
    <path class="solution__pulse" pathLength="1" d="M168 133h56q22 0 22 22v28h31m62 0h42q22 0 22-22v-28h34"/>
    <rect class="solution__panel" x="49" y="91" width="136" height="85" rx="10"/>
    <rect class="solution__tile" x="64" y="105" width="28" height="28" rx="6"/>
    <path class="solution__wire" d="M72 113h12m-12 6h12m-12 6h8"/>
    <text class="solution__ui-text" x="103" y="124">Trigger</text>
    <text class="solution__ui-small" x="64" y="156">FORM RECEIVED</text>
    <rect class="solution__accent" x="250" y="136" width="105" height="94" rx="19"/>
    <path class="solution__ink" d="m277 169 16 14-16 14m24-28 16 14-16 14M274 211h49"/>
    <rect class="solution__panel" x="431" y="91" width="127" height="85" rx="10"/>
    <circle class="solution__tile" cx="455" cy="119" r="12"/>
    <path class="solution__wire" d="m449 119 4 4 8-9"/>
    <text class="solution__ui-text" x="476" y="124">Action</text>
    <text class="solution__ui-small" x="446" y="156">CRM UPDATED</text>
    <rect class="solution__panel" x="86" y="239" width="127" height="46" rx="8"/>
    <circle class="solution__tile" cx="108" cy="262" r="9"/>
    <path class="solution__wire" d="m104 262 3 3 5-6"/>
    <text class="solution__ui-text" x="126" y="267">Approval</text>
    <rect class="solution__panel" x="365" y="243" width="162" height="43" rx="21"/>
    <circle class="solution__accent" cx="387" cy="264" r="5"/>
    <text class="solution__ui-text" x="403" y="269">All connected</text>
  </g>
<?php endswitch ?>
</svg>
