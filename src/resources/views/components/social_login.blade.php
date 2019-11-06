<style>
.social-logins {
	display: flex;
    align-items: center;
    flex-direction: column;
}
.btn.btn-social {
    display: flex;
    padding: 10px 0 10px 10px!important;
    border: 0;
    border-radius: 3px!important;
    width: 100%;
    max-width: 220px;
}
.btn-facebook {
    color: #fff;
    background-color: #3b5998;
    border-color: rgba(0,0,0,0.2);
}
.btn-facebook:hover {
    color: #fff;
    background-color: #30497c;
}
.btn-google {
    color: #fff;
    background-color: #dd4b39;
    border-color: rgba(0,0,0,0.2);
}
.btn-google:hover {
    color: #fff;
    background-color: #c74333;
}
.btn-social > :first-child {
    border: 0;
    line-height: 20px;
    width: 48px!important;
    border-right: 0!important;
    fill: #FFF;
}
</style>
<div class="social-logins">
	@if(Config::get('services.facebook.client_id'))
    <a href="{{ url('/login/facebook') }}" class="btn btn-block btn-social btn-facebook" onclick="_gaq.push(['_trackEvent', 'btn-social', 'click', 'btn-facebook']);">
        <i>
        	<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 32 32">
        		<path d="M19 6h5v-6h-5c-3.86 0-7 3.14-7 7v3h-4v6h4v16h6v-16h5l1-6h-6v-3c0-0.542 0.458-1 1-1z"></path>
        	</svg>
        </i> Facebook
    </a>
    @endif
    @if(Config::get('services.google.client_id'))
    <a href="{{ url('/login/google') }}" class="btn btn-block btn-social btn-google" onclick="_gaq.push(['_trackEvent', 'btn-social', 'click', 'btn-google']);">
        <i>
        	<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 32 32">
				<path d="M16.319 13.713v5.487h9.075c-0.369 2.356-2.744 6.9-9.075 6.9-5.463 0-9.919-4.525-9.919-10.1s4.456-10.1 9.919-10.1c3.106 0 5.188 1.325 6.375 2.469l4.344-4.181c-2.788-2.612-6.4-4.188-10.719-4.188-8.844 0-16 7.156-16 16s7.156 16 16 16c9.231 0 15.363-6.494 15.363-15.631 0-1.050-0.113-1.85-0.25-2.65l-15.113-0.006z"></path>
			</svg>
        </i> Google
    </a>
    @endif
</div>
