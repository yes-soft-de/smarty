<?php

/*-----------------------------------------------------------------------------------*/
/*	Accordion Config
/*-----------------------------------------------------------------------------------*/
$r = rand(0,999);
$vibe_shortcodes['accordion'] = array(
    'params' => array(),
    'no_preview' => true,
    'params' => array(
        'open_first' => array(
			'type' => 'select',
			'label' => __('Open first', 'vibe-shortcodes'),
			'desc' => __('First accordion will be open by default', 'vibe-shortcodes'),
			'options' => array(
				0 => __('No','vibe-shortcodes'),
				1 => __('Yes','vibe-shortcodes'),
			)
		),
    ),
    'shortcode' => '[agroup first="{{open_first}}" connect="'.$r.'"] {{child_shortcode}}  [/agroup]',
    'popup_title' => __('Insert Accordion Shortcode', 'vibe-shortcodes'),
    'child_shortcode' => array(
        'params' => array(
            'title' => array(
			'type' => 'text',
			'label' => __('Accordion Title 1', 'vibe-shortcodes'),
			'desc' => __('Add the title of the accordion', 'vibe-shortcodes'),
			'std' => 'Title'
		),
		'content' => array(
			'std' => 'Content',
			'type' => 'textarea',
			'label' => __('Accordion Content', 'vibe-shortcodes'),
			'desc' => __('Add the content. Accepts HTML & other Shortcodes.', 'vibe-shortcodes'),
		),
              ),
        'shortcode' => '[accordion title="{{title}}" connect="'.$r.'"] {{content}} [/accordion]',
        'clone_button' => __('Add Accordion Toggle', 'vibe-shortcodes')
    )
);

/*-----------------------------------------------------------------------------------*/
/*	Button Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['button'] = array(
	'no_preview' => false,
	'params' => array(
		'url' => array(
			'std' => '',
			'type' => 'text',
			'label' => __('Link URL', 'vibe-shortcodes'),
			'desc' => __('Add the button\'s url eg http://www.example.com', 'vibe-shortcodes')
		),
        'class' => array(
			'std' => '',
			'type' => 'select_hide',
			'label' => __('Button Style', 'vibe-shortcodes'),
			'desc' => __('Select button style', 'vibe-shortcodes'),
                        'options' => array(
				'' => 'Base',
				'primary' => 'Primary',
				'blue' => 'Blue',
				'green' => 'Green',
                'other' => 'Custom',
			),
            'level' => 7
		),
		'bg' => array(
			'type' => 'color',
			'label' => __('Background color', 'vibe-shortcodes'),
			'desc' => __('Select the button\'s size', 'vibe-shortcodes')
		),
                'hover_bg' => array(
			'type' => 'color',
			'label' => __('Hover Bg color', 'vibe-shortcodes'),
			'desc' => __('Select the button\'s on hover background color ', 'vibe-shortcodes')
		),
                'color' => array(
			'type' => 'color',
			'label' => __('Text color', 'vibe-shortcodes'),
			'desc' => __('Select the button\'s text color', 'vibe-shortcodes')
		),
                'size' => array(
			'type' => 'slide',
			'label' => __('Font Size', 'vibe-shortcodes'),
                        'min' => 0,
                        'max' => 100,
                        'std' => 0,
		),
		'width' => array(
			'type' => 'slide',
			'label' => __('Width', 'vibe-shortcodes'),
                        'min' => 0,
                        'max' => 500,
                        'std' => 0,
		),
                'height' => array(
			'type' => 'slide',
			'label' => __('Height', 'vibe-shortcodes'),
                        'min' => 0,
                        'max' => 100,
                        'std' => 0,
		),
		'radius' => array(
			'type' => 'slide',
			'label' => __('Border Radius', 'vibe-shortcodes'),
                        'min' => 0,
                        'max' => 150,
                        'std' => 0
		),
		'target' => array(
			'type' => 'select',
			'label' => __('Button Target', 'vibe-shortcodes'),
			'desc' => __('_self = open in same window. _blank = open in new window', 'vibe-shortcodes'),
			'options' => array(
				'_self' => '_self',
				'_blank' => '_blank'
			)
		),
            'content' => array(
			'std' => '',
			'type' => 'text',
			'label' => __('Button Anchor', 'vibe-shortcodes'),
			'desc' => __('Replace button label with the text you enter.', 'vibe-shortcodes'),
		)
	),
	'shortcode' => '[button url="{{url}}" class="{{class}}" bg="{{bg}}" hover_bg="{{hover_bg}}" size="{{size}}" color="{{color}}" radius="{{radius}}" width="{{width}}"  height="{{height}}"  target="{{target}}"] {{content}} [/button]',
	'popup_title' => __('Insert Button Shortcode', 'vibe-shortcodes')
);


/*-----------------------------------------------------------------------------------*/
/*	Columns Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['columns'] = array(
	'params' => array(),
	'shortcode' => ' {{child_shortcode}} ', // as there is no wrapper shortcode
	'popup_title' => __('Insert Columns Shortcode', 'vibe-shortcodes'),
	'no_preview' => true,
	
	// child shortcode is clonable & sortable
	'child_shortcode' => array(
		'params' => array(
			'column' => array(
				'type' => 'select',
				'label' => __('Column Type', 'vibe-shortcodes'),
				'desc' => __('Select the type, ie width of the column.', 'vibe-shortcodes'),
				'options' => array(
                    'one_fifth' => 'One Fifth',
                    'one_fourth' => 'One Fourth',
					'one_third' => 'One Third',
                    'two_fifth' => 'Two Fifth',
					'one_half' => 'One Half',
                    'three_fifth' => 'Three Fifth',
                    'two_third' => 'Two Thirds',
					'three_fourth' => 'Three Fourth',
                    'four_fifth' => 'Four Fifth',
				)
			),
                        'first' => array(
				'type' => 'select',
				'label' => __('Column Type', 'vibe-shortcodes'),
				'desc' => __('Select the type, ie width of the column.', 'vibe-shortcodes'),
				'options' => array(
                                        '' => 'Default',
                                        'first' => 'First in Row (from Left)',
				)
			),
			'content' => array(
				'std' => '',
				'type' => 'textarea',
				'label' => __('Column Content', 'vibe-shortcodes'),
				'desc' => __('Add the column content.', 'vibe-shortcodes'),
			)
		),
		'shortcode' => '[{{column}} first={{first}}] {{content}} [/{{column}}] ',
		'clone_button' => __('Add Column', 'vibe-shortcodes')
	)
);

/*-----------------------------------------------------------------------------------*/
/*	Counter Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['counter'] = array(
	'no_preview' => true,
	'params' => array(
        'min' => array(
			'std' => 0,
			'type' => 'number',
			'label' => __('Start value of counter', 'vibe-shortcodes'),
			'desc' => __('Add a starting number', 'vibe-shortcodes'),
		),
		'max' => array(
			'std' => 100,
			'type' => 'number',
			'label' => __('Maximum value of counter', 'vibe-shortcodes'),
			'desc' => __('Add the Tooltip text', 'vibe-shortcodes'),
		),
		'delay' => array(
			'std' => 3,
			'type' => 'number',
			'label' => __('Total delay in finishing counter', 'vibe-shortcodes'),
			'desc' => __('Add the total duration of counter increment', 'vibe-shortcodes'),
		),
		'increment' => array(
			'std' => 1,
			'type' => 'number',
			'label' => __('Increment unit', 'vibe-shortcodes'),
			'desc' => __('Increment the counter by this value', 'vibe-shortcodes'),
		),
	),
	'shortcode' => '[number_counter min="{{min}}" max="{{max}}" delay="{{delay}}" increment="{{increment}}"]',
	'popup_title' => __('Insert Counter Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	Countdown Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['countdown'] = array(
	'no_preview' => true,
	'params' => array(
		'date' => array(
			'std' => '',
			'type' => 'date',
			'label' => __('Countdown Date','vibe-shortcodes'),
			'desc' => __('Date until the countdown timer will run', 'vibe-shortcodes'),
		),
        'days' => array(
			'std' => 0,
			'type' => 'number',
			'label' => __('Countdown Days', 'vibe-shortcodes'),
			'desc' => __('Number of days in the countdown timer', 'vibe-shortcodes'),
		),
		'hours' => array(
			'std' => 0,
			'type' => 'number',
			'label' => __('Countdown hours', 'vibe-shortcodes'),
			'desc' => __('Number of hours in the countdown timer', 'vibe-shortcodes'),
		),
		'minutes' => array(
			'std' => 0,
			'type' => 'number',
			'label' => __('Countdown minutes', 'vibe-shortcodes'),
			'desc' => __('Number of minutes in the countdown timer', 'vibe-shortcodes'),
		),
		'seconds' => array(
			'std' => 0,
			'type' => 'number',
			'label' => __('Countdown seconds', 'vibe-shortcodes'),
			'desc' => __('Number of seconds in the countdown timer', 'vibe-shortcodes'),
		),
		'size' => array(
			'std' => 1,
			'type' => 'number',
			'label' => __('Timer Size', 'vibe-shortcodes'),
			'desc' => __('Size of the timer', 'vibe-shortcodes'),
		),
	),
	'shortcode' => '[countdown_timer date="{{date}}" days="{{days}}" hours="{{hours}}" minutes="{{minutes}}" seconds="{{seconds}}" size="{{size}}"]',
	'popup_title' => __('Insert Countdown Shortcode', 'vibe-shortcodes')
);
/*-----------------------------------------------------------------------------------*/
/*	Icon Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['icons'] = array(
	'no_preview' => true,
	'params' => array(
		'icon' => array(
					'type' => 'icon',
					'label' => __('Icon type', 'vibe-shortcodes'),
					'desc' => __('Select Icon type', 'vibe-shortcodes'),
					
                 ),
                 'size' => array(
					'type' => 'slide',
					'label' => __('Icon Size', 'vibe-shortcodes'),
					'desc' => __('Icon Size', 'vibe-shortcodes'),
					'min' => 0,
                    'max' => 100,
                    'std' => 0,
                 ),
                 
                 'class' => array(
					'std' => '',
					'type' => 'select_hide',
					'label' => __('Custom Style', 'vibe-shortcodes'),
					'desc' => __('icon style', 'vibe-shortcodes'),
                    'options' => array(
								'' => 'Text Style',
                                'other' => 'Custom',
					),
		            'level' => 6
				),
                 'color' => array(
					'type' => 'color',
					'label' => __('Icon Color', 'vibe-shortcodes'),
					'desc' => __('Icon Color', 'vibe-shortcodes')
                 )
                 ,
                 'bg' => array(
					'type' => 'color',
					'label' => __('Icon Bg Color', 'vibe-shortcodes'),
					'desc' => __('Icon Background color', 'vibe-shortcodes'),
                 ),
                 'hovercolor' => array(
					'type' => 'color',
					'label' => __('Icon Hover Color', 'vibe-shortcodes'),
					'desc' => __('Icon Color', 'vibe-shortcodes'),
                 )
                 ,
                 'hoverbg' => array(
					'type' => 'color',
					'label' => __('Icon Hover Bg Color', 'vibe-shortcodes'),
					'desc' => __('Icon Background color', 'vibe-shortcodes'),
                 ),
                 'padding' => array(
					'type' => 'slide',
					'label' => __('Icon padding', 'vibe-shortcodes'),
					'desc' => __('Icon Background padding', 'vibe-shortcodes'),
					'min' => 0,
                                        'max' => 100,
                                        'std' => 0,
                 ),
                 'radius' => array(
					'type' => 'slide',
					'label' => __('Icon Bg Radius', 'vibe-shortcodes'),
					'desc' => __('Icon Background radius', 'vibe-shortcodes'),
					'min' => 0,
                                        'max' => 100,
                                        'std' => 0,
                 ),
                 
		
	),
	'shortcode' => '[icon icon="{{icon}}" size="{{size}}" color="{{color}}" bg="{{bg}}" hovercolor="{{hovercolor}}" hoverbg="{{hoverbg}}" padding="{{padding}}" radius="{{radius}}"]',
	'popup_title' => __('Insert Icon Shortcode', 'vibe-shortcodes')
);


/*-----------------------------------------------------------------------------------*/
/*	Alert Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['alert'] = array(
	'no_preview' => true,
	'params' => array(
		'style' => array(
			'type' => 'select_hide',
			'label' => __('Alert Style', 'vibe-shortcodes'),
			'desc' => __('Select the alert\'s style, ie the alert colour', 'vibe-shortcodes'),
			'options' => array(
				'block' => 'Orange',
				'info' => 'Blue',
				'error' => 'Red',
				'success' => 'Green',
                                'other' => 'Custom'
			),
                        'level' => 3
		),
            'bg' => array(
					'type' => 'color',
					'label' => __('Alert Bg Color', 'vibe-shortcodes'),
					'desc' => __('Background color', 'vibe-shortcodes'),
                 ),
            'border' => array(
					'type' => 'color',
					'label' => __('Alert Border Color', 'vibe-shortcodes'),
					'desc' => __('Border color', 'vibe-shortcodes'),
                 ),
            'color' => array(
					'type' => 'color',
					'label' => __('Text Color', 'vibe-shortcodes'),
					'desc' => __('Alert Text color', 'vibe-shortcodes'),
                 ),
		'content' => array(
			'std' => 'Your Alert/Information Message!',
			'type' => 'textarea',
			'label' => __('Alert Text', 'vibe-shortcodes'),
			'desc' => __('Add the alert\'s text', 'vibe-shortcodes'),
		)
		
	),
	'shortcode' => '[alert style="{{style}}" bg="{{bg}}" border="{{border}}" color="{{color}}"] {{content}} [/alert]',
	'popup_title' => __('Insert Alert Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	Tooltip Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['tooltip'] = array(
	'no_preview' => true,
	'params' => array(
        'tip' => array(
			'std' => 'Tip content!',
			'type' => 'textarea',
			'label' => __('Tooltip Text', 'vibe-shortcodes'),
			'desc' => __('Add the Tooltip text', 'vibe-shortcodes'),
		),
		'content' => array(
			'std' => 'Tooltip',
			'type' => 'text',
			'label' => __('Tooltip Anchor', 'vibe-shortcodes'),
			'desc' => __('Add the Tooltip anchor', 'vibe-shortcodes'),
		),
		
	),
	'shortcode' => '[tooltip tip="{{tip}}"] {{content}} [/tooltip]',
	'popup_title' => __('Insert Tooltip Shortcode', 'vibe-shortcodes')
);



/*-----------------------------------------------------------------------------------*/
/*	RoundProgressBar
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['roundprogress'] = array(
	'no_preview' => true,
	'params' => array(
		'percentage' => array(
			'type' => 'text',
			'label' => __('Percentage Cover', 'vibe-shortcodes'),
			'desc' => __('Only number eg:20', 'vibe-shortcodes'),
			'std' => '20'
		),
                'style' => array(
			'type' => 'select',
			'label' => __('Style', 'vibe-shortcodes'),
			'desc' => __('Tron or Custom', 'vibe-shortcodes'),
			'options' => array(
				'' => 'Tron',
				'other' => 'Custom'
			)
		),
                'radius' => array(
			'std' => '200',
			'type' => 'text',
			'label' => __('Circle Diameter', 'vibe-shortcodes'),
			'desc' => __('In pixels eg: 100', 'vibe-shortcodes'),
		),
                'thickness' => array(
			'std' => '20',
			'type' => 'text',
			'label' => __('Circle Thickness', 'vibe-shortcodes'),
			'desc' => __('In percentage', 'vibe-shortcodes'),
		),
                 'color' => array(
					'type' => 'color',
					'label' => __('Progress  Text Color', 'vibe-shortcodes'),
					'desc' => __('Progress  Text color', 'vibe-shortcodes'),
                 ),
                 'bg_color' => array(
					'type' => 'color',
					'label' => __('Progress Circle Color', 'vibe-shortcodes'),
					'desc' => __('Progress Circle color', 'vibe-shortcodes'),
                 ),
		'content' => array(
			'std' => '20%',
			'type' => 'text',
			'label' => __('Some Content', 'vibe-shortcodes'),
			'desc' => __('like : 20% Skill, shortcodes/html allowed', 'vibe-shortcodes'),
		),
		
	),
	'shortcode' => '[roundprogress style="{{style}}" color="{{color}}" bg_color="{{bg_color}}" percentage="{{percentage}}" radius="{{radius}}" thickness="{{thickness}}"] {{content}} [/roundprogress]',
	'popup_title' => __('Insert Round Progress Shortcode', 'vibe-shortcodes')
);



/*-----------------------------------------------------------------------------------*/
/*	ProgressBar
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['progressbar'] = array(
	'no_preview' => true,
	'params' => array(
		'percentage' => array(
			'type' => 'text',
			'label' => __('Percentage Cover', 'vibe-shortcodes'),
			'desc' => __('Only number eg:20', 'vibe-shortcodes'),
			'std' => '20'
		),
		'content' => array(
			'std' => '20%',
			'type' => 'text',
			'label' => __('Some Content', 'vibe-shortcodes'),
			'desc' => __('like : 20% Skill, shortcodes/html allowed', 'vibe-shortcodes'),
		),
		'color' => array(
			'type' => 'select_hide',
			'label' => __('Color', 'vibe-shortcodes'),
			'desc' => __('Select progressbar color', 'vibe-shortcodes'),
			'options' => array(
				'' => 'Default',
                'other' => 'Custom',
			),
                        'level' => 2
		),

        'bar_color' => array(
			'type' => 'color',
			'label' => __('Bar Color', 'vibe-shortcodes'),
			'desc' => __('Bar color', 'vibe-shortcodes'),
         ),
        'bg' => array(
			'type' => 'color',
			'label' => __('Bar Background Color', 'vibe-shortcodes'),
			'desc' => __('Bar Background color', 'vibe-shortcodes'),
         ),
	),
	'shortcode' => '[progressbar color="{{color}}" percentage="{{percentage}}" bg={{bg}} bar_color={{bar_color}}] {{content}} [/progressbar]',
	'popup_title' => __('Insert Progressbar Shortcode', 'vibe-shortcodes')
);


/*-----------------------------------------------------------------------------------*/
/*	Tabs Config
/*-----------------------------------------------------------------------------------*/
$r = rand(0,999);
$vibe_shortcodes['tabs'] = array(
    'params' => array(),
    'no_preview' => true,
    'params' => array(
            'style' => array(
                'std' => '',
                'type' => 'select',
                'label' => __('Tabs Style', 'vibe-shortcodes'),
                'desc' => __('select a style', 'vibe-shortcodes'),
                'options' => array(
                    '' => 'Top Horizontal',
                    'tabs-left' => 'Left Vertical',
                    'tabs-right' => 'Right Vertical'
                )
            ),
            'theme' => array(
                'std' => '',
                'type' => 'select',
                'label' => __('Tabs theme', 'vibe-shortcodes'),
                'desc' => __('select a theme', 'vibe-shortcodes'),
                'options' => array(
                    '' => 'Light',
                    'dark' => 'Dark'
                )
            ),
        ),
    'shortcode' => '[tabs style="{{style}}" theme={{theme}} connect="'.$r.'"] {{child_shortcode}}  [/tabs]',
    'popup_title' => __('Insert Tab Shortcode', 'vibe-shortcodes'),
    
    'child_shortcode' => array(
        'params' => array(
            'title' => array(
                'std' => 'Title',
                'type' => 'text',
                'label' => __('Tab Title', 'vibe-shortcodes'),
                'desc' => __('Title of the tab', 'vibe-shortcodes'),
            ),  
            'icon' => array(
            			'type' => 'icon',
            			'label' => __('Title Icon', 'vibe-shortcodes'),
            			'desc' => __('Select Icon type', 'vibe-shortcodes'),
            			),   
            'content' => array(
                'std' => 'Tab Content',
                'type' => 'textarea',
                'label' => __('Tab Content', 'vibe-shortcodes'),
                'desc' => __('Add the tabs content', 'vibe-shortcodes')
            )
        ),
        'shortcode' => '[tab title="{{title}}" icon="{{icon}}" connect="'.$r.'"] {{content}} [/tab]',
        'clone_button' => __('Add Tab', 'vibe-shortcodes')
    )
);


/*-----------------------------------------------------------------------------------*/
/*	Note Config
/*-----------------------------------------------------------------------------------*/


$vibe_shortcodes['note'] = array(
	'no_preview' => true,
	'params' => array(
            
		'style' => array(
				'std' => 'default',
				'type' => 'select_hide',
				'label' => __('Background Color', 'vibe-shortcodes'),
				'desc' => __('Background color & theme of note', 'vibe-shortcodes'),
                                'options' => array(
					'' => 'Default',
                                        'other' => 'Custom'
				),
                                'level' => 3
			),
                'bg' => array(
                        'label' => 'Background Color',
                        'desc'  => 'Background color',
                        'type'  => 'color'
                ),
                'border' => array(
                        'label' => 'Border Color',
                        'desc'  => 'border color',
                        'type'  => 'color'
                ),
                'color' => array(
                        'label' => 'Text Color',
                        'desc'  => 'text color',
                        'type'  => 'color'
                ),
		'content' => array(
			'std' => '',
			'type' => 'textarea',
			'label' => __('Content', 'vibe-shortcodes'),
			'desc' => __('Note Content, supports HTML/Shortcodes', 'vibe-shortcodes'),
		)
		
	),
	'shortcode' => '[note style="{{style}}" bg="{{bg}}" border="{{border}}" bordercolor="{{bordercolor}}" color="{{color}}"] {{content}} [/note]',
	'popup_title' => __('Insert Note Shortcode', 'vibe-shortcodes')
);


/*-----------------------------------------------------------------------------------*/
/*	DIVIDER Config
/*-----------------------------------------------------------------------------------*/


$vibe_shortcodes['divider'] = array(
	'no_preview' => true,
	'params' => array(
		'style' => array(
				'std' => 'clear',
				'type' => 'text',
				'label' => __('Divider Class', 'vibe-shortcodes'),
				'desc' => __('clear : To begin form new line. Change Size using : one_third,one_fourth,one_fifth,two_third. Use multiple styles space saperated', 'vibe-shortcodes'),
			)
		
	),
	'shortcode' => '[divider style="{{style}}"]',
	'popup_title' => __('Insert Divider Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	Tagline Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['tagline'] = array(
	'no_preview' => true,
	'params' => array(
		'style' => array(
			'type' => 'select_hide',
			'label' => __('Tagline Style', 'vibe-shortcodes'),
			'desc' => __('Select the Tagline style', 'vibe-shortcodes'),
			'options' => array(
				'boxed' => 'Boxed',
				'tagfullwidth' => 'Fullwidth',
                                'other' => 'Custom Boxed'
			),
                    'level' => 4
                    ),
                'bg' => array(
                        'label' => 'Background Color',
                        'desc'  => 'Background color',
                        'type'  => 'color'
                ),
                'border' => array(
                        'label' => 'Overall Border Color',
                        'desc'  => 'border color',
                        'type'  => 'color'
                ),
                'bordercolor' => array(
                        'label' => 'Left Border Color',
                        'desc'  => 'Default color : Theme Primary color',
                        'type'  => 'color'
                ),
                'color' => array(
                        'label' => 'Text Color',
                        'desc'  => 'Default color : Theme text color',
                        'type'  => 'color'
                ),
		'content' => array(
			'std' => 'Tagline Supports HTML',
			'type' => 'textarea',
			'label' => __('Tagline', 'vibe-shortcodes'),
			'desc' => __('Supports HTML content', 'vibe-shortcodes'),
		)
		
	),
	'shortcode' => '[tagline style="{{style}}" bg="{{bg}}" border="{{border}}" bordercolor="{{bordercolor}}" color="{{color}}"] {{content}} [/tagline]',
	'popup_title' => __('Insert Tagline Shortcode', 'vibe-shortcodes')
);



/*-----------------------------------------------------------------------------------*/
/*	Popupss Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['popups'] = array(
	'no_preview' => true,
	'params' => array(
                'id' => array(
                'std' =>'',
				'type' => 'text',
				'label' => __('Enter Popup ID', 'vibe-shortcodes'),
			),  
                'classes' => array(
                                'type' => 'select',
                                'label' => __('Anchor Style', 'vibe-shortcodes'),
                                'options' => array(
				    'default' => 'Default',
		                    'btn' =>  'Button',
		                    'btn primary' =>  'Primary Button',
                                        )
                                    ),    
                    'content' => array(
                        'std' =>'',
			'type' => 'textarea',
			'label' => __('Popup/Modal Anchor', 'vibe-shortcodes'),
			'desc' => __('Supports HTML & Shortcodes', 'vibe-shortcodes')
			),
		    'auto' => array(
                        'std' =>'',
			'type' => 'select',
			'label' => __('Show Popup on Page-load', 'vibe-shortcodes'),
                        'options' => array(1 => 'Yes',0 => 'No')
			), 
		
	),
	'shortcode' => '[popup id="{{id}}" auto="{{auto}}" classes="{{classes}}"] {{content}} [/popup] ',
	'popup_title' => __('Insert Popups Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	Testimonials Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['testimonial'] = array(
	'no_preview' => true,
	'params' => array(
                'id' => array(
                'std' =>'',
				'type' => 'text',
				'label' => __('Enter Testimonial ID', 'vibe-shortcodes'),
			),
             	'length' => array(
                'std' =>'100',
				'type' => 'text',
				'label' => __('Number of Characters to show', 'vibe-shortcodes'),
                'desc' => __('If number of characters entered above is less than Testimonial Post length, Read more link will appear', 'vibe-shortcodes'), 
			),
	),
	'shortcode' => '[testimonial id="{{id}}" length={{length}}]',
	'popup_title' => __('Insert Testimonial Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	COURSE Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['course'] = array(
	'no_preview' => true,
	'params' => array(
                'id' => array(
                'std' =>'',
				'type' => 'text',
				'label' => __('Enter Course ID', 'vibe-shortcodes'),
			),
	),
	'shortcode' => '[course id="{{id}}"]',
	'popup_title' => __('Insert Course Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	PULLQUOTE Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['pullquote'] = array(
	'no_preview' => true,
	'params' => array(
                'style' => array(
                        'std' =>'',
			'type' => 'select',
			'label' => __('Select Side', 'vibe-shortcodes'),
                        'options' => array(
                            'left' => 'LEFT',
                            'right' => 'RIGHT'
                        )
			),
            'content' => array(
					'type' => 'textarea',
					'label' => __('Content', 'vibe-shortcodes'),	
                    ),
	),
	'shortcode' => '[pullquote style="{{style}}"]{{content}}[/pullquote]',
	'popup_title' => __('Insert PullQuote Shortcode', 'vibe-shortcodes')
);


/*-----------------------------------------------------------------------------------*/
/*	TEAM MEMBER Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['team_member'] = array(
	'no_preview' => true,
	'params' => array(
                'pic' => array(
			'std' => '',
			'type' => 'text',
			'label' => __('Member Image', 'vibe-shortcodes'),
			'desc' => __('Image url of team member', 'vibe-shortcodes'),
		),
		'name' => array(
			'std' => '',
			'type' => 'text',
			'label' => __('Member Name', 'vibe-shortcodes'),
			'desc' => __('Name of team member (HTML allowed)', 'vibe-shortcodes'),
		),
        'designation' => array(
			'std' => '',
			'type' => 'text',
			'label' => __('Designation', 'vibe-shortcodes'),
			'desc' => __('Designation of Team Member (HTML allowed)', 'vibe-shortcodes'),
		),
        ),
        'shortcode' => '[team_member pic="{{pic}}" name="{{name}}" designation="{{designation}}"] {{child_shortcode}}  [/team_member]',
        'popup_title' => __('Insert Team Member Shortcode', 'vibe-shortcodes'),
        'child_shortcode' => array(
        'params' => array(
                'icon' => array(
					'type' => 'socialicon',
					'label' => __('Social Icon', 'vibe-shortcodes'),	
                    ),
            'url' => array(
						'std' => 'http://www.vibethemes.com',
						'type' => 'text',
						'label' => __('Icon Link', 'vibe-shortcodes'),
                    )
                ),
        'shortcode' => '[team_social url="{{url}}" icon="{{icon}}"]',
        'clone_button' => __('Add Social Information', 'vibe-shortcodes')
                )
    );


/*-----------------------------------------------------------------------------------*/
/*	Google Maps Config
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['maps'] = array(
	'no_preview' => true,
	'params' => array(
		'map' => array(
			'std' => '',
			'type' => 'textarea',
			'label' => __('End Map Iframe code', 'vibe-shortcodes'),
			'desc' => __('Enter your map iframce code including iframe tags', 'vibe-shortcodes'),
		)
		
	),
	'shortcode' => '[map]{{map}}[/map]',
	'popup_title' => __('Insert Google Maps Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	Gallery Config
/*-----------------------------------------------------------------------------------*/

                        
$vibe_shortcodes['gallery'] = array(
	'no_preview' => true,
	'params' => array(
                
		'size' => array(
		                'std' =>'',
			'type' => 'select',
			'label' => __('Select Thumb Size', 'vibe-shortcodes'),
			'desc' => __('Image size', 'vibe-shortcodes'),
			'options' => array(
			                        '' => 'Select Size',
			                        'normal' => 'Normal',
			                        'small' => 'Small',
			                        'micro' => 'Very Small',
			                        'large' => 'Large'
			            )
		),
		
                'ids' => array(
			'std' => '',
			'type' => 'text',
			'label' => __('Attachment Ids', 'vibe-shortcodes'),
			'desc' => __('Attachment Ids separated by comma', 'vibe-shortcodes'),
		)
		
	),
	'shortcode' => '[gallery size="{{size}}" ids="{{ids}}"]',
	'popup_title' => __('Insert Gallery Shortcode', 'vibe-shortcodes')
);

/*-----------------------------------------------------------------------------------*/
/*	Social Icons
/*-----------------------------------------------------------------------------------*/


$vibe_shortcodes['socialicons'] = array(
	'no_preview' => true,
	'params' => array(
		'icon' => array(
					'type' => 'socialicon',
					'label' => __('Social Icon', 'vibe-shortcodes'),
					'desc' => __('Select Elastic Social Icon, takes size/color of text it is inserted in:', 'vibe-shortcodes'),
				),	
				'size' => array(
					'std' => '32',
					'type' => 'text',
					'label' => __('Size in pixels', 'vibe-shortcodes'),
					'desc' => __('Enter Elastic font size in pixels ', 'vibe-shortcodes'),
				),
				),
				
				        'shortcode' => '[socialicon icon="{{icon}}" size="{{size}}"]',
				        'popup_title' => __('Insert Social Icon Shortcode', 'vibe-shortcodes')
			);
/*-----------------------------------------------------------------------------------*/
/*	Forms
/*-----------------------------------------------------------------------------------*/



$vibe_shortcodes['forms'] = array(
	'no_preview' => true,
	'params' => array(
					'forms' => array(
						'std' => __('Contact Form','vibe-shortcodes'),
						'type' => 'conditional',
						'label' => __('Select Form Type','vibe-shortcodes'),
						'desc' => '',
						'options' => array(
                            '' => 'Contact Form',
                            'event' => 'Event Form',
                        ),
                        'condition'=>array(
                        	''=>array(
                        		'to'=>'vibe_show',
                        		'subject'=>'vibe_show',
                        		'event'=>'vibe_hide'
                    		),
                        	'event'=>array(
                        		'to'=>'vibe_hide',
                        		'subject'=>'vibe_hide',
                        		'event'=>'vibe_show'
                    		),
                    	),
					),
                    'to' => array(
						'std' => 'example@example.com',
						'type' => 'text',
						'label' => __('Enter email', 'vibe-shortcodes'),
						'desc' => __('Email is sent to this email. Use comma for multiple entries', 'vibe-shortcodes'),
					),
                    'subject' => array(
						'std' => 'Subject',
						'type' => 'text',
						'label' => __('Email Subject', 'vibe-shortcodes'),
						'desc' => __('Subject of email', 'vibe-shortcodes'),
					),
					'event' => array(
						'std' => '',
						'type' => 'text',
						'label' => __('Enter custom event trigger', 'vibe-shortcodes'),
						'desc' => __('This event is triggerred when this form is submitted', 'vibe-shortcodes'),
					),
				),
	'shortcode' => '[form to="{{to}}" subject="{{subject}}" event="{{event}}"] {{child_shortcode}}  [/form]',
    'popup_title' => __('Generate Contact Form Shortcode', 'vibe-shortcodes'),
    'child_shortcode' => array(
        'params' => array(
                    'placeholder' => array(
						'std' => 'Name',
						'type' => 'text',
						'label' => __('Label Text', 'vibe-shortcodes'),
						'desc' => __('Add the content. Accepts HTML & other Shortcodes.', 'vibe-shortcodes'),
                    ),
                    'type' => array(
						'type' => 'select',
						'label' => __('Form Element', 'vibe-shortcodes'),
						'desc' => __('select Form element type', 'vibe-shortcodes'),
						'options' => array(
                            'text' => 'Single Line Text Box (Text)',
                            'textarea' => 'Multi Line Text Box (TextArea)',
                            'select' => 'Select from Options (Select)',
                            'checkbox' => 'Checkbox',
                            'captcha' => 'Captcha field',
                            'upload' => 'Upload File',
                            'submit' => 'Submit Button'
                        )
                    ),
		            'options' => array(
						'std' => '',
						'type' => 'text',
						'label' => __('Enter Select Options', 'vibe-shortcodes'),
						'desc' => __('Comma seperated options.', 'vibe-shortcodes'),
		            ),
		            'upload_options' => array(
						'std' => '',
						'type' => 'multiselect',
						'label' => __('Select File Extensions', 'vibe-shortcodes'),
						'desc' => __('select file extensions for upload type.', 'vibe-shortcodes'),
						'options' => array(
                            'PDF' => 'PDF',
                            'TEXT' => 'TEXT',
                            'DOC' => 'DOC',
                            'DOCx' => 'DOCX',
                            'PPT' => 'PPT',
                            'PPTX' => 'PPTX',
                            'ZIP' => 'ZIP',
                            'PNG' => 'PNG',
                            'JPG' => 'JPG',
                            'JPEG' => 'JPEG'
                        ),
		            ),
		            'validate' => array(
						'type' => 'select',
						'label' => __('Validation', 'vibe-shortcodes'),
						'desc' => __('select Form element type', 'vibe-shortcodes'),
						'options' => array(
	                            '' => 'None',
	                            'required' => 'Required',
	                            'email' => 'Email',
	                            'numeric' => 'Numeric',
	                            'phone' => 'Phone Number'
	                        )
                    ),
                    
              ),
        'shortcode' => '[form_element type="{{type}}" validate="{{validate}}" options="{{options}}" upload_options="{{upload_options}}" placeholder="{{placeholder}}"]',
        'clone_button' => __('Add Form Element', 'vibe-shortcodes')
    )
);	


/*-----------------------------------------------------------------------------------*/
/*	HEADING
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['heading'] = array(
	'no_preview' => true,
	'params' => array(
		'content' => array(
			'std' => '',
			'type' => 'textarea',
			'label' => __('Enter Heading', 'vibe-shortcodes'),
			'desc' => __('Enter heading.', 'vibe-shortcodes')
                    )
		),
	'shortcode' => '[heading] {{content}} [/heading]',
	'popup_title' => __('Insert Heading Shortcode', 'vibe-shortcodes')
);					

/*-----------------------------------------------------------------------------------*/
/*	VIDEO
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['iframevideo'] = array(
	'no_preview' => true,
	'params' => array(
		'content' => array(
			'std' => '',
			'type' => 'textarea',
			'label' => __('Enter Video iframe Code', 'vibe-shortcodes'),
			'desc' => __('For Responsive iframe videos form Youtube, Vimeo,bliptv etc...', 'vibe-shortcodes')
                    )
		),
	'shortcode' => '[iframevideo] {{content}} [/iframevideo]',
	'popup_title' => __('Insert iFrame Video Shortcode', 'vibe-shortcodes')
);					

/*-----------------------------------------------------------------------------------*/
/*	IFRAME
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['iframe'] = array(
	'no_preview' => true,
	'params' => array(
		'height' => array(
					'std' => '600',
					'type' => 'text',
					'label' => __('Enter Iframe Height', 'vibe-shortcodes'),
					'desc' => __('Set iframe height', 'vibe-shortcodes'),
				),
		'content' => array(
			'std' => '',
			'type' => 'textarea',
			'label' => __('Enter iframe URL', 'vibe-shortcodes'),
			'desc' => __('For Responsive iframe based content, like Articulate storyline, iSpring content etc...', 'vibe-shortcodes')
                    )
		),
	'shortcode' => '[iframe height={{height}}] {{content}} [/iframe]',
	'popup_title' => __('Insert iFrame Shortcode', 'vibe-shortcodes')
);	

/*-----------------------------------------------------------------------------------*/
/*	SURVEY RESULT
/*-----------------------------------------------------------------------------------*/

$vibe_shortcodes['survey_result'] = array(
	'no_preview' => true,
	'params' => array(
		'quiz_id' => array(
					'std' => '',
					'type' => 'number',
					'label' => __('Enter Quiz id (optional)', 'vibe-shortcodes'),
					'desc' => __('Quiz id for which Survey results are to be displayed.', 'vibe-shortcodes'),
				),
		'user_id' => array(
					'std' => '',
					'type' => 'number',
					'label' => __('Enter User id (optional)', 'vibe-shortcodes'),
					'desc' => __('User id for which Survey results are to be displayed.', 'vibe-shortcodes'),
				),
		'lessthan' => array(
					'std' => '',
					'type' => 'number',
					'label' => __('Enter result Upper limit ', 'vibe-shortcodes'),
					'desc' => __('Message to be displayed if survey score is less than this value', 'vibe-shortcodes'),
				),
		'greaterthan' => array(
					'std' => '',
					'type' => 'number',
					'label' => __('Enter result Lower limit ', 'vibe-shortcodes'),
					'desc' => __('Message to be displayed if survey score is more than this value', 'vibe-shortcodes'),
				),
		'content' => array(
			'std' => '',
			'type' => 'textarea',
			'label' => __('Enter Survey result message', 'vibe-shortcodes'),
			'desc' => __('Enter message for result', 'vibe-shortcodes')
                    )
		),
	'shortcode' => '[survey_result user_id={{user_id}} quiz_id={{quiz_id}} lessthan={{lessthan}} greaterthan={{greaterthan}}] {{content}} [/survey_result]',
	'popup_title' => __('Insert Survey Result Shortcode in Quiz completion message', 'vibe-shortcodes')
);	

?>