jQuery(document).ready(function ()
{
	jQuery('select[name="category"]').on('change',function(){
		var category = jQuery(this).val();
		if(category)
		{
			jQuery.ajax({
				url : '../../get_sub_category',
				type: 'GET',
				dataType: 'json',
				data: { id: category },
				success:function(data)
				{
					console.log(data);

					jQuery('select[name="sub_category"]').empty();
					$('select[name="sub_category"]').append('<option value="">'+ "Select" +'</option>');
					jQuery.each(data, function(key,value){
						console.log(value.name)
						$('select[name="sub_category"]').append('<option value="'+ value.id +'">'+ value.name +'</option>');
					});					
					
				}
			});
		}
	});
});

jQuery(document).ready(function ()
{
	jQuery('select[name="sub_category"]').on('change',function(){
		var sub_category = jQuery(this).val();
		var category = jQuery("#category").val();
		if(sub_category)
		{
			jQuery.ajax({
				url : '../../get_product',
				type: 'GET',
				dataType: 'json',
				data: { sub_category: sub_category, category: category },
				success:function(data)
				{
					console.log(data);

					jQuery('select[name="product"]').empty();
					$('select[name="product"]').append('<option value="">'+ "Select" +'</option>');
					jQuery.each(data, function(key,value){
						console.log(value.name)
						$('select[name="product"]').append('<option value="'+ value.id +'">'+ value.name +'</option>');
					});					
					
				}
			});
		}
	});
});

jQuery(document).ready(function ()
{
	jQuery('select[name="product"]').on('change',function(){
		var product = jQuery(this).val();
		if(product)
		{
			jQuery.ajax({
				url : '../../get_product_detail',
				type: 'GET',
				dataType: 'json',
				data: { product: product},
				success:function(data)
				{
					console.log(data);
					document.getElementById("unit").value = data.metric.name;
					document.getElementById("available").value = data.quantity;				
					
				}
			});
		}
	});
});