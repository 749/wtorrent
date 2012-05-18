<div id="messages_box"{if $web->getMessages() != ""} style="display: block;"{else} style="display: none;"{/if}>
	<div id="messages_t">
		<div id="close_m">
			
		</div>
	</div>
	<div id="messages_m">
		<div id="messages">
			{$web->getMessages()}
		</div>
	</div>
	<div id="messages_b">

	</div>
</div>
{literal}
<script type="text/javascript">
<!--
	$('close_m').onclick = (function (frame) { return function () { var f = $(frame); if('fade' in f) { f.fade(); } else { f.hide(); } } })('messages_box');
//-->
</script>
{/literal}