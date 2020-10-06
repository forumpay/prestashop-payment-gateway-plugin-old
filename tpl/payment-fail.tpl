{extends file='page.tpl'}

{block name='page_content'}

<h3 class="h1 card-title">
                Payment Failed
              </h3>
<div class="error">
	{l s='Unfortunately, an error occurred during the transaction.' mod='forumpay'}<br /><br />
	{l s='If you need further assistance, feel free to contact us anytime.' mod='forumpay'}<br /><br />
{if isset($forumpay_order.reference)}
	({l s='Your Order\'s Reference:' mod='forumpay'} <b>{$forumpay_order.reference|escape:html:'UTF-8'}</b>)
{else}
	({l s='Your Order\'s ID:' mod='forumpay'} <b>{$forumpay_order.id|escape:html:'UTF-8'}</b>)
{/if}
</div>
{/block}			
