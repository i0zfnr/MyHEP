@php
    $floatingAiProvider = config('services.gemini.key') ? 'gemini' : (config('services.openai.key') ? 'openai' : 'deepseek');
    $floatingAiModel = (string) config("services.{$floatingAiProvider}.model");
    $floatingAiReady = (bool) config("services.{$floatingAiProvider}.key");
@endphp
<style>
    .admin-ai-fab { position:fixed; right:24px; bottom:24px; z-index:1060; width:58px; height:58px; border:1px solid color-mix(in srgb,var(--se-primary) 58%,var(--border)); border-radius:18px; background:linear-gradient(145deg,var(--se-primary-button-start),var(--se-primary-button-end)); color:var(--se-primary-button-text); box-shadow:0 18px 38px color-mix(in srgb,var(--se-primary) 28%,transparent); display:grid; place-items:center; cursor:pointer; transition:transform .18s ease,box-shadow .18s ease,opacity .18s ease; }
    .admin-ai-fab:hover { transform:translateY(-3px); box-shadow:0 22px 44px rgba(35,24,16,.34); }
    .admin-ai-fab svg { width:26px; height:26px; }
    .admin-ai-fab-dot { position:absolute; right:-2px; top:-2px; width:13px; height:13px; border-radius:50%; background:#38b76b; border:3px solid var(--surface); }
    .admin-ai-popover { position:fixed; right:24px; bottom:94px; z-index:1061; width:min(390px,calc(100vw - 28px)); height:min(590px,calc(100dvh - 130px)); display:grid; grid-template-rows:auto minmax(0,1fr) auto; border:1px solid color-mix(in srgb,var(--se-primary) 35%,var(--border)); border-radius:22px; overflow:hidden; background:color-mix(in srgb,var(--surface) 94%,transparent); box-shadow:0 28px 70px rgba(20,14,10,.34); backdrop-filter:blur(22px) saturate(135%); opacity:0; visibility:hidden; transform:translateY(14px) scale(.98); transform-origin:bottom right; transition:.2s ease; }
    .admin-ai-popover.is-open { opacity:1; visibility:visible; transform:none; }
    .admin-ai-pop-head { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:15px 16px; border-bottom:1px solid var(--border); background:linear-gradient(135deg,color-mix(in srgb,var(--primary) 16%,var(--surface)),var(--surface)); }
    .admin-ai-pop-title { display:flex; align-items:center; gap:10px; min-width:0; }
    .admin-ai-pop-icon { width:38px; height:38px; border-radius:12px; display:grid; place-items:center; background:var(--se-primary-soft); color:var(--se-primary-strong); font-size:18px; }
    .admin-ai-pop-icon svg { width:21px; height:21px; }
    .admin-ai-pop-title strong,.admin-ai-pop-title small { display:block; }
    .admin-ai-pop-title small { color:var(--text-muted); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:230px; }
    .admin-ai-close { border:0; background:transparent; color:var(--text-muted); font-size:26px; cursor:pointer; }
    .admin-ai-pop-log { overflow:auto; padding:15px; display:flex; flex-direction:column; gap:10px; background:color-mix(in srgb,var(--surface) 88%,var(--background)); }
    .admin-ai-pop-msg { max-width:88%; padding:11px 13px; border-radius:14px; font-size:.84rem; line-height:1.55; white-space:pre-wrap; overflow-wrap:anywhere; }
    .admin-ai-pop-msg.ai { align-self:flex-start; color:var(--text); background:var(--surface); border:1px solid var(--border); border-bottom-left-radius:5px; }
    .admin-ai-pop-msg.user { align-self:flex-end; color:#fff; background:linear-gradient(135deg,#526f8d,#354c65); border-bottom-right-radius:5px; }
    .admin-ai-pop-msg.error { align-self:flex-start; color:#b42318; background:#fff1f0; border:1px solid #f1aaa4; }
    .admin-ai-pop-compose { padding:12px; border-top:1px solid var(--border); background:var(--surface); }
    .admin-ai-pop-row { display:grid; grid-template-columns:1fr 44px; gap:8px; }
    .admin-ai-pop-input { min-width:0; min-height:44px; max-height:110px; resize:none; border:1px solid var(--border); border-radius:13px; padding:11px 12px; background:var(--background); color:var(--text); font:inherit; }
    .admin-ai-pop-send { border:0; border-radius:13px; background:linear-gradient(145deg,var(--se-primary-button-start),var(--se-primary-button-end)); color:var(--se-primary-button-text); font-weight:900; cursor:pointer; }
    .admin-ai-pop-send:disabled { opacity:.55; cursor:wait; }
    .admin-ai-pop-link { display:inline-block; margin-top:8px; color:var(--se-primary-strong); font-size:.74rem; font-weight:800; text-decoration:none; }
    @media(max-width:640px){
        .admin-ai-fab { right:10px; bottom:16px; width:48px; height:48px; border-radius:16px; box-shadow:0 12px 28px color-mix(in srgb,var(--se-primary) 30%,transparent); }
        .admin-ai-fab svg { width:22px; height:22px; }
        .admin-ai-popover { right:8px; bottom:74px; width:calc(100vw - 16px); height:min(540px,calc(100dvh - 92px)); border-radius:20px; }
        .admin-ai-pop-head { padding:12px; }
        .admin-ai-pop-log { min-height:0; padding:12px; }
        .admin-ai-pop-compose { padding:10px; }
        body.admin-ai-chat-open .admin-ai-fab { opacity:0; pointer-events:none; }
    }
    @media (max-width:767px) and (display-mode:standalone),
           (max-width:767px) and (display-mode:fullscreen),
           (max-width:767px) and (display-mode:minimal-ui),
           (max-width:767px) and (display-mode:window-controls-overlay) {
        body.student-bottom-nav-eligible .admin-ai-fab { bottom:calc(86px + env(safe-area-inset-bottom,0px)); }
        body.student-bottom-nav-eligible .admin-ai-popover { bottom:calc(144px + env(safe-area-inset-bottom,0px)); height:min(520px,calc(100dvh - 162px - env(safe-area-inset-bottom,0px))); }
    }
</style>

<button type="button" class="admin-ai-fab" id="adminAiFab" aria-controls="adminAiPopover" aria-expanded="false" aria-label="{{ __('Open AI Helper') }}">
    @include('partials.ai_helper_icon', ['class' => 'admin-ai-fab-icon'])
    @if($floatingAiReady)<span class="admin-ai-fab-dot" aria-hidden="true"></span>@endif
</button>
<section class="admin-ai-popover" id="adminAiPopover" aria-hidden="true">
    <header class="admin-ai-pop-head">
        <div class="admin-ai-pop-title"><span class="admin-ai-pop-icon">@include('partials.ai_helper_icon')</span><span><strong>{{ __('Admin AI Helper') }}</strong><small>{{ strtoupper($floatingAiProvider) }} · {{ $floatingAiModel }}</small></span></div>
        <button type="button" class="admin-ai-close" id="adminAiClose" aria-label="{{ __('Close') }}">×</button>
    </header>
    <div class="admin-ai-pop-log" id="adminAiPopLog" aria-live="polite"><div class="admin-ai-pop-msg ai">{{ __('Ask a quick question without leaving this page.') }}</div></div>
    <footer class="admin-ai-pop-compose">
        <div class="admin-ai-pop-row"><textarea class="admin-ai-pop-input" id="adminAiPopInput" rows="1" maxlength="2000" placeholder="{{ __('Ask about current system records...') }}" @disabled(!$floatingAiReady)></textarea><button type="button" class="admin-ai-pop-send" id="adminAiPopSend" @disabled(!$floatingAiReady)>➤</button></div>
        <a class="admin-ai-pop-link" href="{{ route('admin.ai-helper.index') }}">{{ __('Open full AI workspace') }} →</a>
    </footer>
</section>
<script>
(() => {
    const fab=document.getElementById('adminAiFab'), panel=document.getElementById('adminAiPopover'), close=document.getElementById('adminAiClose'), log=document.getElementById('adminAiPopLog'), input=document.getElementById('adminAiPopInput'), send=document.getElementById('adminAiPopSend');
    if(!fab||!panel||!log||!input||!send)return;
    const toggle=(open)=>{panel.classList.toggle('is-open',open);panel.setAttribute('aria-hidden',open?'false':'true');fab.setAttribute('aria-expanded',open?'true':'false');document.body.classList.toggle('admin-ai-chat-open',open);if(open)input.focus()};
    fab.addEventListener('click',()=>toggle(!panel.classList.contains('is-open'))); close?.addEventListener('click',()=>toggle(false));
    document.addEventListener('keydown',e=>{if(e.key==='Escape')toggle(false)});
    const add=(type,text)=>{const node=document.createElement('div');node.className=`admin-ai-pop-msg ${type}`;node.textContent=text;log.appendChild(node);log.scrollTop=log.scrollHeight;return node};
    const ask=async()=>{const message=input.value.trim();if(!message)return;add('user',message);input.value='';input.disabled=true;send.disabled=true;const waiting=add('ai',@json(__('Thinking...')));try{const response=await fetch(@json(route('admin.ai-helper.ask')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||''},credentials:'same-origin',body:JSON.stringify({message,template:'floating-chat',filters:{}})});const data=await response.json().catch(()=>({}));waiting.remove();if(!response.ok)throw new Error(data.message||@json(__('AI request failed.')));add('ai',data.answer||@json(__('No answer was returned.')))}catch(error){waiting.remove();add('error',error.message||@json(__('AI service could not be reached.')))}finally{input.disabled=false;send.disabled=false;input.focus()}};
    send.addEventListener('click',ask);input.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();ask()}});
})();
</script>
