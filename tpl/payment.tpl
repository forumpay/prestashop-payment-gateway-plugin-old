{extends file='page.tpl'}

{block name='page_content'}

<link href="{$css_url|escape:'html':'UTF-8'}" rel="stylesheet" type="text/css"/>
<div class="row" align="center">
<section>
<div class="forumpay-main">
<div class="forumpay-row forumpay-row-img">
 <img src="{$basepath}/css/forumpay-logo.png"  alt="Pay with Crypto (by ForumPay)" />	
</div>	
	
<div class="forumpay-row forumpay-title" id="forumpay-ccy-div">			
    <select name="ChangeCurrency" onChange="forumpaygetrate(this.value)">
		<option value='0'>--Select Cryptocurrency--</option>		
		{foreach from=$currencylist item=item}		
<option value='{$item['code']}'>{$item['desc']}</option>
{/foreach}		
    </select>
</div>

<div class="fp-details" style="display: none" id="fp-details-div">	

<div class="forumpay-row">	
<div class="forumpay-col1">Order No</div>
<div class="forumpay-col2"><snap id="forumpay-ordno">0</snap></div>
</div>
	
<div class="forumpay-row">
<div class="forumpay-col1">Order Amount:</div>
<div class="forumpay-col2">	
<snap id="forumpay-ordamt">0</snap>
</div>	
</div>	
	
<div class="forumpay-rowsm">
<div class="forumpay-col1">Rate:</div>
<div class="forumpay-col2">	
<snap id="forumpay-exrate"> </snap>
</div>							  
</div>	
	
<div class="forumpay-rowsm">
<div class="forumpay-col1">Exchange amount:</div>
<div class="forumpay-col2">	
<snap id="forumpay-examt"> </snap>
</div>	
</div>	
<div class="forumpay-rowsm">
<div class="forumpay-col1">Network processing fee:</div>
<div class="forumpay-col2">	
<snap id="forumpay-netpfee"> </snap>
</div>	
</div>	
<div class="forumpay-row">
<div class="forumpay-col1">Total:</div>
<div class="forumpay-col2">	
<snap id="forumpay-tot"> </snap>
</div>	
</div>	
	
<div class="forumpay-rowsm" id="forumpay-wtime-div">
<div class="forumpay-col1">Expected time to wait:</div>
<div class="forumpay-col2">	
<snap id="forumpay-waittime"> </snap>
</div>	
</div>

<div class="forumpay-rowsm" id="forumpay-txfee-div">
<div class="forumpay-col1">TX fee set to:</div>
<div class="forumpay-col2">	
<snap id="forumpay-txfee"> </snap>
</div>	
</div>	
	
	
<div class="forumpay-row forumpay-qr" style="display: none" id="qr-img-div">	
<img src="" id="forumpay-qr-img" style="width: 50%">		
</div>
<div class="forumpay-row forumpay-addr">
  <snap id="forumpay-addr"></snap>
</div>	

<div class="forumpay-row forumpay-addr" id="forumpay-btn-div">
  <button type="button" id="forumpay-payment-btn" class="paybtn" style="width:90%;" onclick="forumpaystart()">
Start payment</button>
</div>
	
</div>		

<div class="forumpay-row forumpay-st" id='forumpay-payst-div' style="display: none">
  Status : 
  <snap id="forumpay-payst"></snap>
</div>	

<div class="forumpay-row forumpay-err" id='forumpay-err-div' style="display: none">
  Error : 
  <snap id="forumpay-err"> </snap>
</div>	
		
</div>		
<div id="forumpay-loading" style="display: none">
  <img id="forumpay-loading-image" src="{$basepath}/css/page-load.gif" alt="Loading..." />
</div>		
</section>

{if isset($errmsg)}
<div class="alert alert-warning">{$errmsg|escape:'html':'UTF-8'}</div>
{/if}

<script type="text/javascript">
//<![CDATA[
	var chamount = 0;
	var chaddr = '';
	var chpaymentid = '';	
	var fpcurrency = '';		
	var timeerstar = '';
	var fpTimer;
	
	function forumpaystart(){
				
		forumpaygetqrcode();
		
	}

	function forumpaygetrate(currency){
		if (currency == '0') return;
		var ajaxurl = "{$getrateurl}";	
		
	var data = {									
			currency     : currency,
		}		
	
	

		
 	
		
	 $('#qr-img-div').hide();
     $('#forumpay-err-div').hide();
	 $('#forumpay-loading').show();		
		
	jQuery.ajax({
        type: "POST",
		data: data,
        url: ajaxurl,
        success:function(rdata)
        {			
			 $('#forumpay-loading').hide();			
			var response_json = jQuery.parseJSON(rdata);			
				if (response_json.status == 'Yes') {
				$('#forumpay-addr').text(response_json.addr);
				
    						
				$('#forumpay-ordno').text(response_json.orderid);	
				$('#forumpay-ordamt').text(response_json.ordamt);						
				$('#forumpay-tot').text(response_json.amount);						
				$('#forumpay-exrate').text(response_json.exrate);						
				$('#forumpay-examt').text(response_json.examt);					
				$('#forumpay-netpfee').text(response_json.netpfee);	
				$('#forumpay-waittime').text(response_json.waittime);					
				$('#forumpay-txfee').text(response_json.txfee);							
				$('#fp-details-div').show();							
				
				
				fpcurrency = currency ;
				chaddr = response_json.addr ;
				chamount = response_json.amount ;			
				chpaymentid = response_json.payment_id ;						
				if (timeerstar == '') {		
					timeerstar = 'start';
				 	clearInterval(fpTimer);
				 	fpTimer = setInterval(forumpayratest, 10000);
				}
			}
			else {
				$('#forumpay-err-div').show();
				$('#forumpay-err').text(response_json.errmgs);
			}
		},
		
		error: function(xhr, textStatus, errorThrown){
			 	$('#forumpay-loading').hide();			
				clearInterval(fpTimer);
       			alert('API Request fail');
    	}
	}
		)
	
}	

function forumpayratest()
{
	forumpaygetrate(fpcurrency);
}	
	
function forumpaygetqrcode(){
	
		if (fpcurrency == '') return;
	clearInterval(fpTimer);
    timeerstar = 'qr';			
var ajaxurl = "{$getqrurl}";	
		
	var data = {									
			currency     : fpcurrency,
		}		
	 $('#qr-img-div').hide();
     $('#forumpay-err-div').hide();
	 $('#forumpay-loading').show();		
	$('#forumpay-btn-div').hide();		
	$('#forumpay-ccy-div').hide();		
		
	jQuery.ajax({
        type: "POST",
		data: data,
        url: ajaxurl,
        success:function(rdata)
        {			
			 $('#forumpay-loading').hide();			
			var response_json = jQuery.parseJSON(rdata);			
				if (response_json.status == 'Yes') {
				$('#forumpay-addr').text(response_json.addr);
				
    			$('#forumpay-qr-img').prop('src', response_json.qr_img); 
				$('#forumpay-ordno').text(response_json.orderid);						
				$('#forumpay-ordamt').text(response_json.ordamt);	
				$('#forumpay-tot').text(response_json.amount);						
				$('#forumpay-exrate').text(response_json.exrate);						
				$('#forumpay-examt').text(response_json.examt);					
				$('#forumpay-netpfee').text(response_json.netpfee);	
				$('#forumpay-waittime').text(response_json.waittime);					
				$('#forumpay-txfee').text(response_json.txfee);							
				$('#qr-img-div').show();				
				
				chaddr = response_json.addr ;
				chamount = response_json.amount ;			
				chpaymentid = response_json.payment_id ;	
				if (timeerstar == 'qr') {	
				 	timeerstar = 'start';	
					clearInterval(fpTimer);
					fpTimer = setInterval(getstaus, 10000);
				}
			}
			else {
				$('#forumpay-err-div').show();
				$('#forumpay-err').text(response_json.errmgs);
			}
		}
	}
		)
	
	return false;
}	
	
function getstaus(){	
	var ajaxurl = "{$getstausurl}";	
	var data = {
			currency       : fpcurrency,			
			addr       : chaddr,
			amount       : chamount,
			paymentid       : chpaymentid
		}	
	$('#forumpay-loading').show();	
    jQuery.ajax({
        type: "POST",
		data: data,
        url: ajaxurl,
        success:function(response)
        {
			$('#forumpay-loading').hide();				
			response_json = jQuery.parseJSON(response);			
            if(response_json.status == "Yes") {
			 window.location.href = response_json.purl;
			}
			else {
				$('#forumpay-payst').text(response_json.status);
				$('#forumpay-payst-div').show();					
			}
        
        }
    });
}
//]]>
            </script>	
</div>	
{/block}			