{*

{$var1}

{if $bootstrap}{/if}
{if $bootstrap}{else}{/if}

{foreach from=$terminals key=iter item=terminal}{/foreach}

{l s='Just some string' mod='mymodule'}
{l s='ZIP code %s is:' mod='mymodule' sprintf=$zip}

{$customer_points|escape:'htmlall':'UTF-8'}

{cycle values="#FFF6CF,#FFFFFF"} Alternates every 2 cycles 1, 0, 1, 0, 1

*}