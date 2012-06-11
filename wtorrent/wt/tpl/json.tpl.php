{if $web->isRegistered()}
	{$web->getJson()}
{else}
	{$str.log_in}
{/if}