jQuery(document).ready(function($){
	jQuery('body').on('vibeapp_grid_selected',function(){
		if(jQuery('body').find('.grid_value') && jQuery('body').find('.grid_value').val()){
			var val = $.parseJSON(jQuery('body').find('.grid_value').val()) ;
			console.log(val);
			if(val){
				val['type'] = 'grid_update';
				$('body').find('.grid-rows').val(val.rows);
				$('body').find('.grid-columns').val(val.columns);
				$('body').trigger('update_playground',[{details:val}]);

			}
			
		}
	});
	
	

	$('body').delegate('.reset_grid','click',function(){
		$('.grid-columns').trigger('keyup');
	});

	$('body').delegate('.grid-rows,.grid-columns','change',function(event){
		$('.grid-columns').trigger('keyup');
		
	});
	let gridjson = [];
	$('body').delegate('.grid-rows,.grid-columns','keyup',function(event){
		gridjson = [];
		var $this = $(this);
		var parent = $this.closest('.grid_field_wrapper');
		var rows = parseInt(parent.find('.grid-rows').val());
		var columns = parseInt(parent.find('.grid-columns').val());
		var playground = parent.find('.playground');

		if(rows && columns){

			for (var i = 1; i <= rows; i++) {
				for (var j = 1; j<=columns; j++) {
					gridjson.push({col:j,row:i});
				}
			}
			console.log(playground);
			$('body').trigger('update_playground',[{'details':{type:'grid_update','grid':gridjson,'rows':rows,'columns':columns}}]);
		}
	});


	$('body').on('update_playground',function(event,data){
		let $this = $('.playground');
		$this.html('');
		if(data.details.type == 'grid_update'){
			if(data.details && Array.isArray(data.details.grid)){
				gridjson = data.details.grid;
				data.details.grid.map(function(item){
					$this.append('<span class="grid_element" style="grid-column:'+item.col+';grid-row:'+item.row+'" data-col="'+item.col+'" data-row="'+item.row+'"></span>');
				});
				jQuery('body').find('.grid_value').val(JSON.stringify({'rows':data.details.rows,'columns':data.details.columns,'grid':data.details.grid}));
				jQuery('body').find('.grid_value').trigger('input');
				$('body').trigger('active_playground',[{'details':{type:'grid_update','rows':data.details.rows,'columns':data.details.columns,'grid':data.details.grid}}]);
			}
		}
	});

	$('body').on('active_playground',function(event,data){
		let $this = $('.playground');

		$this.find('.grid_element').on('click',function(){
			let gridthis = $(this);
			if($this.find('.grid_element.active').length){

				let activecol = parseInt($this.find('.grid_element.active').attr('data-col'));
				let activerow = parseInt($this.find('.grid_element.active').attr('data-row'));
				let ngridjson = data.details.grid;
				let index = ngridjson.findIndex(function(item){return item.col == activecol && item.row == activerow});

				let items_tobe_removed= [];
				let newarr  = [];
				ngridjson.map(function(item,i){
					
					console.log(item.col+'>='+(activecol) +'&&'+item.col+'<='+ (parseInt(gridthis.attr('data-col')))+'---------'+i);


					if((item.col >= activecol && item.col <= parseInt(gridthis.attr('data-col')) ) &&
						item.row >= activerow && item.row <= parseInt(gridthis.attr('data-row'))
						){
						if(index !== i){
							items_tobe_removed.push(i);
							//ngridjson.splice(i,1);
							//console.log('spliced',ngridjson);
						}else{
							newarr.push(item);
						}
					}else{
						newarr.push(item);
					}
				});
				
				console.log(index+'>>>>>'+activecol+'/'+(parseInt(gridthis.attr('data-col')))+'----'+activerow+'/'+(parseInt(gridthis.attr('data-row'))+1));
				ngridjson[index].col=activecol+'/'+(parseInt(gridthis.attr('data-col'))+1);
				ngridjson[index].row=activerow+'/'+(parseInt(gridthis.attr('data-row'))+1);

				$this.find('.grid_element.active').removeClass('active');

				console.log(newarr);
				
				$('body').trigger('update_playground',[{'details':{type:'grid_update','grid':newarr,'rows':data.details.rows,'columns':data.details.columns}}]);
			}else{
				console.log('checkl');
				if(!isNaN(gridthis.attr('data-col'))){
					gridthis.addClass('active');
				}
			}
			
		});
		
	});
			
});	