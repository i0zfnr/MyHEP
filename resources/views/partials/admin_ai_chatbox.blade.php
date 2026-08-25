@php
    $floatingAiProvider = config('services.gemini.key') ? 'gemini' : (config('services.openai.key') ? 'openai' : 'deepseek');
    $floatingAiModel = (string) config("services.{$floatingAiProvider}.model");
    $floatingAiReady = (bool) config("services.{$floatingAiProvider}.key");
@endphp


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
