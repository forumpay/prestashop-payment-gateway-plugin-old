{$forumpay_confirmation}
<link href="{$module_dir}css/forumpay.css" rel="stylesheet" type="text/css">

<img src="{$forumpay_tracking|escape:'htmlall':'UTF-8'}" alt="" style="display: none;"/>

	<div class="forumpay-header">
		<img src="{$module_dir}logo.png" alt="forumpay" class="forumpay-logo" /></a>
	</div>

	<form action="{$forumpay_form|escape:'htmlall':'UTF-8'}" id="module_form" class="defaultForm form-horizontal" method="post">
<div class="panel" id="fieldset_0">    
<div class="panel-heading">
<i class="icon-cogs"></i>Settings
</div>    

<div class="form-group">            

				<label  class="control-label col-lg-3" for="forumpay_posid">{l s='POS ID:' mod='forumpay'}</label>
<div class="col-lg-3">
<div class="input-group">
<span class="input-group-addon"><i class="icon icon-tag"></i></span>
	<input type="text" class="text" name="forumpay_posid" id="forumpay_posid" value="{$forumpay_posid|escape:'htmlall':'UTF-8'}" />
</div>
</div>
</div>

<div class="form-group">            

				<label  class="control-label col-lg-3" for="forumpay_apiuser">{l s='API User:' mod='forumpay'}</label>
<div class="col-lg-3">
<div class="input-group">
<span class="input-group-addon"><i class="icon icon-tag"></i></span>
	<input type="text" class="text" name="forumpay_apiuser" id="forumpay_apiuser" value="{$forumpay_apiuser|escape:'htmlall':'UTF-8'}" />
</div>
</div>
</div>


<div class="form-group">              
          
				<label class="control-label col-lg-3" for="forumpay_apikey">{l s='API secret:' mod='forumpay'}</label>
<div class="col-lg-3">
<div class="input-group">

<span class="input-group-addon"><i class="icon icon-tag"></i></span>
					<input type="text" class="text" name="forumpay_apikey" id="forumpay_apikey" value="{$forumpay_apikey|escape:'htmlall':'UTF-8'}" >

</div>
</div>
</div>

                
   
<div class="form-group">                    
				<label class="control-label col-lg-3" for="forumpay_order_status">{l s='Success Order Status:' mod='forumpay'}</label>                                
<div class="col-lg-3">
	              <select name="forumpay_order_status" id="input-transaction-method" class="form-control">
					{foreach from=$orderstates key='ordid' item='ordname'}                  
						<option value="{$ordid}" {if $ordid == $forumpay_order_status} selected="selected"{/if}>{$ordname}</option>
					{/foreach}
	              </select>             
</div>
</div>

                


</div>
<div class="panel-footer">                

						<button type="submit" value="1" id="module_form_submit_btn" name="submitforumpay" class="btn btn-default pull-right">
					<i class="process-icon-save"></i> Save
				</button>
</div>        
			</div>
	</form>


