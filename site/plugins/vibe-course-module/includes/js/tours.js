var tour_objects=[];
//active_tours
if(typeof active_tours !== "undefined" ){
    jQuery.each(active_tours,function(key,value){
        jQuery.each(value,function(k,v){
        
            var tour_obj = {
                name: k,
                container: "body",
                steps:window[k],
                keyboard:false,
                onShow: function(tour){
                    //if there is no previous selector in dom then hide it 
                    //code.........
                    if(tour._current == null){tour._current=0;}

                    var dfd = jQuery.Deferred();
                    
                   
                    if(typeof tour._options.steps[(tour._current)] != 'undefined' && tour._options.steps[(tour._current)].hasOwnProperty('orphan') && tour._options.steps[(tour._current)].orphan){
                        dfd.resolve();
                        console.log("start");
                    }else{

                        var max_skip = 0;
                        var loop = 0;

                        setTimeout(function(){
                            max_skip=1;
                        },5000);

                        if(tour._options.steps[tour._current].hasOwnProperty('init_end_step')){
                            if(eval(tour._options.steps[tour._current].init_end_step)){
                                for(let i=0;i<tour._options.steps.length;i++){
                                    if(tour._options.steps[i].hasOwnProperty('start_end')){
                                        tour.goTo(i); 
                                        dfd.resolve();
                                        clearInterval(loop);
                                        return dfd;
                                    }
                                }
                            }
                        }

                        if(tour._options.steps[tour._current].hasOwnProperty('condition') &&  jQuery(tour._options.steps[tour._current].element).length && (tour._options.steps[tour._current].displayed == 'undefined' || !tour._options.steps[tour._current].hasOwnProperty('displayed')) ){

                            if(eval(tour._options.steps[tour._current].condition)){
                                console.log("Evalaute condition");
                                dfd.resolve();
                                clearInterval(loop);
                                return dfd;
                            }else{
                                tour._current++;
                                tour.goTo(tour._current); 
                                console.log("GOTO Step - "+tour._current+" On condition");
                                dfd.resolve();
                                clearInterval(loop);
                                return dfd;
                            }
                        }

                        if(jQuery(tour._options.steps[tour._current].element).length && (tour._options.steps[tour._current].displayed == 'undefined' || !tour._options.steps[tour._current].hasOwnProperty('displayed'))){
                            console.log(tour._options.steps[tour._current].element);
                            dfd.resolve();
                            clearInterval(loop);
                            return dfd;
                        }

                        loop = setInterval(function(){
                            if(jQuery.active == 0 && jQuery(tour._options.steps[tour._current].element).length && (tour._options.steps[tour._current].displayed == 'undefined' || !tour._options.steps[tour._current].hasOwnProperty('displayed'))){
                                console.log('Elemnet exists');
                                if(tour._options.steps[tour._current].hasOwnProperty('condition')){
                                    if(eval(tour._options.steps[tour._current].condition)){
                                        console.log("Evalaute condition");
                                        dfd.resolve();
                                        clearInterval(loop);
                                        return dfd;
                                    }else{
                                        clearInterval(loop);
                                        max_skip = 0;
                                        tour._current++;
                                        tour.goTo(tour._current);
                                    }
                                }else{  
                                    dfd.resolve();
                                    clearInterval(loop);
                                    return dfd;
                                }
                            }else{
                                if(max_skip && jQuery.active == 0){

                                    for(let i=0;i<tour._options.steps.length;i++){
                                        console.log(" Looping "+i);
                                        if(tour._options.steps[i].repeat && jQuery(tour._options.steps[i].element).length 
                                            && (tour._options.steps[i].displayed == 'undefined' || !tour._options.steps[i].hasOwnProperty('displayed'))){
                                                

                                                if(tour._options.steps[i].hasOwnProperty('condition')){
                                                    if(eval(tour._options.steps[i].condition)){
                                                        console.log("Evalaute condition");
                                                        dfd.resolve();
                                                        clearInterval(loop);
                                                        break;
                                                    }
                                                }else{  
                                                    
                                                        console.log(i + ' == '+tour._current);
                                                        clearInterval(loop);
                                                        max_skip = 0;
                                                        tour.goTo(i);
                                                        break;
                                                    
                                                }
                                        }
                                    }

                                }
                            }
                        },500);

                    }
                    return dfd;
                },
                onShown: function(tour){
                    
                    if((tour._current+1) >= tour._options.steps.length){

                        return tour;  
                    } 

                    if(tour._current == null){tour._current=0;}
                    

                    if(tour._options.steps[(tour._current)].hasOwnProperty('need_click')){
                        if(tour._options.steps[(tour._current)].need_click){
                            jQuery('body').find('.tour-'+tour._options.name+'-'+tour._current+' button[data-role="prev"]').hide();
                            jQuery('body').find('.tour-'+tour._options.name+'-'+tour._current+' button[data-role="next"]').hide(); 
                        }
                    }

                    if(typeof tour._options.steps[(tour._current+1)] != 'undefined' && jQuery(tour._options.steps[(tour._current+1)].element).length < 0){
                        jQuery('body').find('.tour-'+tour._options.name+'-'+tour._current+' button[data-role="next"]').hide();
                    }
                    
                    if(typeof tour._options.steps[tour._current-1] !=  'undefined' && tour._options.steps[tour._current-1].length && jQuery(tour._options.steps[tour._current-1].element).length < 0){
                        tour._options.steps[tour._current].prev = -1;
                        console.log('prev hide');
                        jQuery('body').find('.tour-'+tour._options.name+'-'+tour._current+' button[data-role="prev"]').hide();
                    }

                    tour._options.steps[tour._current].displayed = true;
                },
                onNext(tour){
                    
                    var next_promise = jQuery.Deferred();
                    // IF STEP EXISTS
                    if(tour._options.steps.length > tour._current+2){
                        //STEP EXISTS
                        console.log('STEP EXISTS -'+tour._current);
                        return next_promise.resolve();
                    }

                    if(tour._options.steps.length >= tour._current+1){

                        console.log('AJAX FETCH STEP '+(tour._current+1));
                        
                        if(tour._options.steps[(tour._current+1)].hasOwnProperty('ajax')){
                            if(tour._options.steps[(tour._current+1)].ajax){
                               var step_added = jQuery.when( jQuery.active == 0 ).done(function() {
                                    
                                   
                                    jQuery.ajax({
                                        type: "POST",
                                        dataType:'json',
                                        url: ajaxurl,
                                        data: { action: 'tour_next_step', 
                                                tour:JSON.stringify(tour._options),
                                                step:(tour._current+1),
                                                conditions:JSON.stringify(tour._options.conditions)
                                            },
                                        cache: false,
                                        success: function(step){
                                            if(step){
                                                if(jQuery.isArray(step)){
                                                    console.log(step);
                                                        tour.addSteps(step);
                                                }else{
                                                    tour.addStep(step);
                                                }
                                                localStorage.setItem(tour._options.name+'_steps',JSON.stringify(tour._options.steps));
                                            }
                                           
                                        }
                                    });
                                });
                            }
                        }
                        return step_added;
                    }
                },
                onEnd: function (tour) {
                    end_tour_wplms(k);
                },
                template: "<div class='popover tour'>\
                            <div class='arrow'></div><div class='closepop'></div>\
                            <h3 class='popover-title'></h3>\
                            <div class='popover-content'></div>\
                            <div class='popover-navigation'>\
                                <button class='btn btn-default' data-role='prev'><i class='fa fa-arrow-left'></i></button>\
                                <button class='btn btn-default' data-role='next'><i class='fa fa-arrow-right'></i></button>\
                                <button class='btn btn-default' data-role='end'>"+tours_strings.end_tour+"</button>\
                            </div>\
                          </div>",
            };
            if(typeof tour_conditions != 'undefined'){
                tour_obj.conditions = tour_conditions;
            }
            tour_objects[k] = new Tour(tour_obj);
      		tour_objects[k].init();
    		tour_objects[k].start();
            jQuery('body').delegate('.closepop','click',function(event){
                event.preventDefault();
                if((tour_objects[k]._current+1) < tour_objects[k]._options.steps.length){
                tour_objects[k].goTo(tour_objects[k]._current+1);
                }else if(tour_objects[k]._options.steps.length == (tour_objects[k]._current+1)){
                   tour_objects[k].end(); 
                }
                
            });
        });
    });
	}
									