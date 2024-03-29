<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'molkobain-pixbox/1.0.0',
	array(
		// Identification
		//
		'label' => 'Pixbox',
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
			'itop-portal/2.6.0',
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'model.molkobain-pixbox.php',
			'src/Extension/ApplicationObjectExtension.php',
			'src/Brick/ReaderBrick.php',
			'src/Controller/ReaderBrickController.php',
			'src/Router/ReaderBrickRouter.php',
		),
		'webservice' => array(),
		'data.struct' => array(// add your 'structure' definition XML files here,
		),
		'data.sample' => array(// add your sample data XML files here,
		),

		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any

		// Default settings
		//
		'settings' => array(
			'picture_max_width' => 1920,
			'post_reader_days_of_history' => 30,
		),
	)
);
