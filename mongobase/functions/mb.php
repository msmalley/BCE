<?php

function var_dumped($data = array(), $exit = true, $title = false)
{
    echo '<div style="display:block; postiion:relative; padding:20px">';
	if($title) echo '<br /><strong>'.$title.'</strong><br />';
    echo '<pre>';
    print_r($data);
    echo '</pre><br />';
	echo '</div>';
    if($exit) exit;
}

function array_mt_rand($array)
{
    $random_key = mt_rand(0, count($array) - 1);
    $count_key = 0;
    foreach($array as $key => $value)
    {
	if($count_key == $random_key) return $value;
	$count_key++;
    }
}

function mb_include($directory = false, $file = false, $extension = 'php', $default_content = false)
{
	$mb = mb_class();
	$content = $mb->cascade($directory = false, $file = false, $extension = 'php', $default_content = false);
	return $content;
}

function mb_include_class($class = 'mb')
{
	global $mb;
	if(mb_starts_with($class, 'mongobase_'))
	{
		$class_array = explode('mongobase_', $class);
		$class = $class_array[1];
	}
	if(isset($mb['paths']) && isset($mb['paths']['core']))
	{
		$mb_app = dirname(dirname($mb['paths']['app'])).'/classes/';
		$file = $mb_app.$class.'.php';
		if(!file_exists($file)){
			$mb_core = dirname(dirname($mb['paths']['core'])).'/classes/';
			$file = $mb_core.$class.'.php';
		}
		if(file_exists($file))
		{
			include_once($file);
			if(class_exists('mongobase_'.$class))
			{
				// Nothing to see here ...
			}
			else
			{
				$mb_class = mb_class();
				if(is_object($mb_class))
				{
					$mb_class->pretty_print(_('Error'), '<h1>'.sprintf('Missing "%1$s" Class!', $class).'</h1>'.sprintf('It appears that the -- <strong>%1$s class</strong> -- cannot be located within:<br />%2$s', $class, $file));
				}
				else
				{
					$message = $file.' '._('could not be found');
					trigger_error($message, E_USER_NOTICE);
				}
			}
		}
		else
		{
			$mb_class = mb_class();
			if(is_object($mb_class))
			{
				$mb_class->pretty_print(_('Error'), '<h1>'.sprintf('Missing "%1$s" Class!', $class).'</h1>'.sprintf('It appears that -- <strong>%1$s</strong> -- cannot be located', $file));
			}
			else
			{
				$message = $file.' '._('could not be found');
				trigger_error($message, E_USER_NOTICE);
			}
		}
	}
	else
	{
		$mb_class = mb_class();
		if(is_object($mb_class))
		{
			$mb_class->pretty_print(_('Error'), '<h1>'._('Oops!').'</h1>'._('It appears <strong>mb_core</strong> is missing from the global paths!'));
		}
		else
		{
			$message = _('mb_core missing from global paths');
			trigger_error($message, E_USER_NOTICE);
		}
	}
}

// Register auto-loading of classes
//spl_autoload_register('mb_include_class');

function mb_class($class_name = 'mb', $init_options = array())
{
	if(isset($GLOBALS['mb']['classes'][$class_name]))
	{
		return $GLOBALS['mb']['classes'][$class_name];
	}
	$default_init_options = array();
	$default_init = array_merge($default_init_options, $init_options);
	// Confidentally call class using spl_autoload_register functionality
	$qualifed_name = 'mongobase_'.$class_name;
	$class = new $qualifed_name($default_init);
	if(is_object($class)){
		$GLOBALS['mb']['classes'][$class_name] = $class;
		return $class;
	}
}

function mb_class_loaded($class_name)
{
	if(isset($GLOBALS['mb']['classes'][$class_name]))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function mb_countries()
{
	$countries = array(
		''			=> _(' -- Select Country -- '),
		'United States' => _('United States'),
		'United Kingdom' => _('United Kingdom'),
		'Malaysia' => _('Malaysia'),
		'Afghanistan' => _('Afghanistan'),
		'Albania' => _('Albania'),
		'Algeria' => _('Algeria'),
		'American Samoa' => _('American Samoa'),
		'Andorra' => _('Andorra'),
		'Angola' => _('Angola'),
		'Anguilla' => _('Anguilla'),
		'Antarctica' => _('Antarctica'),
		'Antigua and Barbuda' => _('Antigua and Barbuda'),
		'Argentina' => _('Argentina'),
		'Armenia' => _('Armenia'),
		'Aruba' => _('Aruba'),
		'Australia' => _('Australia'),
		'Austria' => _('Austria'),
		'Azerbaijan' => _('Azerbaijan'),
		'Bahamas' => _('Bahamas'),
		'Bahrain' => _('Bahrain'),
		'Bangladesh' => _('Bangladesh'),
		'Barbados' => _('Barbados'),
		'Belarus' => _('Belarus'),
		'Belgium' => _('Belgium'),
		'Belize' => _('Belize'),
		'Benin' => _('Benin'),
		'Bermuda' => _('Bermuda'),
		'Bhutan' => _('Bhutan'),
		'Bolivia' => _('Bolivia'),
		'Bosnia and Herzegovina' => _('Bosnia and Herzegovina'),
		'Botswana' => _('Botswana'),
		'Bouvet Island' => _('Bouvet Island'),
		'Brazil' => _('Brazil'),
		'British Indian Ocean Territory' => _('British Indian Ocean Territory'),
		'Brunei Darussalam' => _('Brunei Darussalam'),
		'Bulgaria' => _('Bulgaria'),
		'Burkina Faso' => _('Burkina Faso'),
		'Burundi' => _('Burundi'),
		'Cambodia' => _('Cambodia'),
		'Cameroon' => _('Cameroon'),
		'Canada' => _('Canada'),
		'Cape Verde' => _('Cape Verde'),
		'Cayman Islands' => _('Cayman Islands'),
		'Central African Republic' => _('Central African Republic'),
		'Chad' => _('Chad'),
		'Chile' => _('Chile'),
		'China' => _('China'),
		'Christmas Island' => _('Christmas Island'),
		'Cocos (Keeling) Islands' => _('Cocos (Keeling) Islands'),
		'Colombia' => _('Colombia'),
		'Comoros' => _('Comoros'),
		'Congo' => _('Congo'),
		'Congo, The Democratic Republic of The' => _('Congo, The Democratic Republic of The'),
		'Cook Islands' => _('Cook Islands'),
		'Costa Rica' => _('Costa Rica'),
		'Cote D\'ivoire' => _('Cote D\'ivoire'),
		'Croatia' => _('Croatia'),
		'Cuba' => _('Cuba'),
		'Cyprus' => _('Cyprus'),
		'Czech Republic' => _('Czech Republic'),
		'Denmark' => _('Denmark'),
		'Djibouti' => _('Djibouti'),
		'Dominica' => _('Dominica'),
		'Dominican Republic' => _('Dominican Republic'),
		'Ecuador' => _('Ecuador'),
		'Egypt' => _('Egypt'),
		'El Salvador' => _('El Salvador'),
		'Equatorial Guinea' => _('Equatorial Guinea'),
		'Eritrea' => _('Eritrea'),
		'Estonia' => _('Estonia'),
		'Ethiopia' => _('Ethiopia'),
		'Falkland Islands (Malvinas)' => _('Falkland Islands (Malvinas)'),
		'Faroe Islands' => _('Faroe Islands'),
		'Fiji' => _('Fiji'),
		'Finland' => _('Finland'),
		'France' => _('France'),
		'French Guiana' => _('French Guiana'),
		'French Polynesia' => _('French Polynesia'),
		'French Southern Territories' => _('French Southern Territories'),
		'Gabon' => _('Gabon'),
		'Gambia' => _('Gambia'),
		'Georgia' => _('Georgia'),
		'Germany' => _('Germany'),
		'Ghana' => _('Ghana'),
		'Gibraltar' => _('Gibraltar'),
		'Greece' => _('Greece'),
		'Greenland' => _('Greenland'),
		'Grenada' => _('Grenada'),
		'Guadeloupe' => _('Guadeloupe'),
		'Guam' => _('Guam'),
		'Guatemala' => _('Guatemala'),
		'Guinea' => _('Guinea'),
		'Guinea-bissau' => _('Guinea-bissau'),
		'Guyana' => _('Guyana'),
		'Haiti' => _('Haiti'),
		'Heard Island and Mcdonald Islands' => _('Heard Island and Mcdonald Islands'),
		'Holy See (Vatican City State)' => _('Holy See (Vatican City State)'),
		'Honduras' => _('Honduras'),
		'Hong Kong' => _('Hong Kong'),
		'Hungary' => _('Hungary'),
		'Iceland' => _('Iceland'),
		'India' => _('India'),
		'Indonesia' => _('Indonesia'),
		'Iran, Islamic Republic of' => _('Iran, Islamic Republic of'),
		'Iraq' => _('Iraq'),
		'Ireland' => _('Ireland'),
		'Israel' => _('Israel'),
		'Italy' => _('Italy'),
		'Jamaica' => _('Jamaica'),
		'Japan' => _('Japan'),
		'Jordan' => _('Jordan'),
		'Kazakhstan' => _('Kazakhstan'),
		'Kenya' => _('Kenya'),
		'Kiribati' => _('Kiribati'),
		'Korea, Democratic People\'s Republic of' => _('Korea, Democratic People\'s Republic of'),
		'Korea, Republic of' => _('Korea, Republic of'),
		'Kuwait' => _('Kuwait'),
		'Kyrgyzstan' => _('Kyrgyzstan'),
		'Lao People\'s Democratic Republic' => _('Lao People\'s Democratic Republic'),
		'Latvia' => _('Latvia'),
		'Lebanon' => _('Lebanon'),
		'Lesotho' => _('Lesotho'),
		'Liberia' => _('Liberia'),
		'Libyan Arab Jamahiriya' => _('Libyan Arab Jamahiriya'),
		'Liechtenstein' => _('Liechtenstein'),
		'Lithuania' => _('Lithuania'),
		'Luxembourg' => _('Luxembourg'),
		'Macao' => _('Macao'),
		'Macedonia, The Former Yugoslav Republic of' => _('Macedonia, The Former Yugoslav Republic of'),
		'Madagascar' => _('Madagascar'),
		'Malawi' => _('Malawi'),
		'Malaysia' => _('Malaysia'),
		'Maldives' => _('Maldives'),
		'Mali' => _('Mali'),
		'Malta' => _('Malta'),
		'Marshall Islands' => _('Marshall Islands'),
		'Martinique' => _('Martinique'),
		'Mauritania' => _('Mauritania'),
		'Mauritius' => _('Mauritius'),
		'Mayotte' => _('Mayotte'),
		'Mexico' => _('Mexico'),
		'Micronesia, Federated States of' => _('Micronesia, Federated States of'),
		'Moldova, Republic of' => _('Moldova, Republic of'),
		'Monaco' => _('Monaco'),
		'Mongolia' => _('Mongolia'),
		'Montenegro' => _('Montenegro'),
		'Montserrat' => _('Montserrat'),
		'Morocco' => _('Morocco'),
		'Mozambique' => _('Mozambique'),
		'Myanmar' => _('Myanmar'),
		'Namibia' => _('Namibia'),
		'Nauru' => _('Nauru'),
		'Nepal' => _('Nepal'),
		'Netherlands' => _('Netherlands'),
		'Netherlands Antilles' => _('Netherlands Antilles'),
		'New Caledonia' => _('New Caledonia'),
		'New Zealand' => _('New Zealand'),
		'Nicaragua' => _('Nicaragua'),
		'Niger' => _('Niger'),
		'Nigeria' => _('Nigeria'),
		'Niue' => _('Niue'),
		'Norfolk Island' => _('Norfolk Island'),
		'Northern Mariana Islands' => _('Northern Mariana Islands'),
		'Norway' => _('Norway'),
		'Oman' => _('Oman'),
		'Pakistan' => _('Pakistan'),
		'Palau' => _('Palau'),
		'Palestinian Territory, Occupied' => _('Palestinian Territory, Occupied'),
		'Panama' => _('Panama'),
		'Papua New Guinea' => _('Papua New Guinea'),
		'Paraguay' => _('Paraguay'),
		'Peru' => _('Peru'),
		'Philippines' => _('Philippines'),
		'Pitcairn' => _('Pitcairn'),
		'Poland' => _('Poland'),
		'Portugal' => _('Portugal'),
		'Puerto Rico' => _('Puerto Rico'),
		'Qatar' => _('Qatar'),
		'Reunion' => _('Reunion'),
		'Romania' => _('Romania'),
		'Russian Federation' => _('Russian Federation'),
		'Rwanda' => _('Rwanda'),
		'Saint Helena' => _('Saint Helena'),
		'Saint Kitts and Nevis' => _('Saint Kitts and Nevis'),
		'Saint Lucia' => _('Saint Lucia'),
		'Saint Pierre and Miquelon' => _('Saint Pierre and Miquelon'),
		'Saint Vincent and The Grenadines' => _('Saint Vincent and The Grenadines'),
		'Samoa' => _('Samoa'),
		'San Marino' => _('San Marino'),
		'Sao Tome and Principe' => _('Sao Tome and Principe'),
		'Saudi Arabia' => _('Saudi Arabia'),
		'Sealand' => _('Sealand'),
		'Senegal' => _('Senegal'),
		'Serbia' => _('Serbia'),
		'Seychelles' => _('Seychelles'),
		'Sierra Leone' => _('Sierra Leone'),
		'Singapore' => _('Singapore'),
		'Slovakia' => _('Slovakia'),
		'Slovenia' => _('Slovenia'),
		'Solomon Islands' => _('Solomon Islands'),
		'Somalia' => _('Somalia'),
		'South Africa' => _('South Africa'),
		'South Georgia and The South Sandwich Islands' => _('South Georgia and The South Sandwich Islands'),
		'South Sudan' => _('South Sudan'),
		'Spain' => _('Spain'),
		'Sri Lanka' => _('Sri Lanka'),
		'Sudan' => _('Sudan'),
		'Suriname' => _('Suriname'),
		'Svalbard and Jan Mayen' => _('Svalbard and Jan Mayen'),
		'Swaziland' => _('Swaziland'),
		'Sweden' => _('Sweden'),
		'Switzerland' => _('Switzerland'),
		'Syrian Arab Republic' => _('Syrian Arab Republic'),
		'Taiwan, Republic of China' => _('Taiwan, Republic of China'),
		'Tajikistan' => _('Tajikistan'),
		'Tanzania, United Republic of' => _('Tanzania, United Republic of'),
		'Thailand' => _('Thailand'),
		'Timor-leste' => _('Timor-leste'),
		'Togo' => _('Togo'),
		'Tokelau' => _('Tokelau'),
		'Tonga' => _('Tonga'),
		'Trinidad and Tobago' => _('Trinidad and Tobago'),
		'Tunisia' => _('Tunisia'),
		'Turkey' => _('Turkey'),
		'Turkmenistan' => _('Turkmenistan'),
		'Turks and Caicos Islands' => _('Turks and Caicos Islands'),
		'Tuvalu' => _('Tuvalu'),
		'Uganda' => _('Uganda'),
		'Ukraine' => _('Ukraine'),
		'United Arab Emirates' => _('United Arab Emirates'),
		'United Kingdom' => _('United Kingdom'),
		'United States' => _('United States'),
		'United States Minor Outlying Islands' => _('United States Minor Outlying Islands'),
		'Uruguay' => _('Uruguay'),
		'Uzbekistan' => _('Uzbekistan'),
		'Vanuatu' => _('Vanuatu'),
		'Venezuela' => _('Venezuela'),
		'Viet Nam' => _('Viet Nam'),
		'Virgin Islands, British' => _('Virgin Islands, British'),
		'Virgin Islands, U.S.' => _('Virgin Islands, U.S.'),
		'Wallis and Futuna' => _('Wallis and Futuna'),
		'Western Sahara' => _('Western Sahara'),
		'Yemen' => _('Yemen'),
		'Zambia' => _('Zambia'),
		'Zimbabwe' => _('Zimbabwe')
	);
	return $countries;
}

function mb_country_name($key)
{
	$countries = false;
	$counts = mb_countries();
	foreach($counts as $country)
	{
		$countries[mb_string_to_slug($country)] = $country;
	}
	return $countries[$key];
}

function mb_starts_with($src, $string, $case=true)
{
    if($case){return (strcmp(substr($src, 0, strlen($string)),$string)===0);}
    return (strcasecmp(substr($src, 0, strlen($string)),$string)===0);
}

function mb_ends_with($src, $string)
{
    $length = strlen($string);
    if ($length == 0) return true;
    else return (substr($src, -$length) === $string);
}

function mb_contains($src, $string, $ignore_case=true)
{
	if ($ignore_case){
        $string = strtolower($string);
        $src = strtolower($src);
    }
    return strpos($src,$string) !== false;
}

function mb_option($class = 'mb', $field = 'id', $default = false)
{
	$obj = mb_class($class);
	$option = $obj->get_option($field, $default);
	return $option;
}

function mb_string_to_slug($src)
{
	$src = strtolower(trim($src));
	$src = preg_replace('/[^a-z0-9-]/', '-', $src);
	$src = preg_replace('/-+/', "-", $src);
	return $src;
}

function mb_slug_to_string($slug, $capitalize = true)
{
	$string = str_replace('blog/', '', $slug);
	$string = str_replace('-kl-', ' KL ', $string);
	$string = str_replace('kl-', 'KL ', $string);
	$string = str_replace('-bc-', ' BC ', $string);
	$string = str_replace('bc-', 'BC ', $string);
	$string = str_replace('-', ' ', $string);
	if($capitalize) $string = ucwords($string);
	return $string;
}