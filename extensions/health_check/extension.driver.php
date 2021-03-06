<?php

	Class extension_health_check extends Extension{
	
		public function fetchNavigation() {
			return array(
				array(
					'location' => 'System',
					'name'	=> 'Health Check',
					'link'	=> '/directories/',
				),
			);
		}
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'addToHead'
				),
		        array(
		            'page'      => '/backend/',
		            'delegate'  => 'DashboardPanelRender',
		            'callback'  => 'render_panel'
		        ),
		        array(
		            'page'      => '/backend/',
		            'delegate'  => 'DashboardPanelTypes',
		            'callback'  => 'dashboard_panel_types'
		        )
			);
		}
		
		public function addToHead($context) {
			$callback = Administration::instance()->getPageCallback();

			// Append assets
			if($callback['driver'] == 'directories' || $callback['classname'] == 'contentExtensionDashboardIndex') {
				Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/health_check/assets/healthcheck.publish.css', 'screen');
			}
		}
		
		public function dashboard_panel_types($context) {
		    $context['types']['health_check_panel'] = __('Health Check Panel');
		}
		
		public function render_panel($context) {
		    if ($context['type'] != 'health_check_panel') return;

			$fetchUniqueuploadfield = ExtensionManager::fetchStatus(array('handle' => 'uniqueuploadfield'));

			if($fetchUniqueuploadfield[0] == EXTENSION_ENABLED) {
				$destinations = Symphony::Database()->fetch("SELECT destination COLLATE utf8_general_ci AS destination FROM tbl_fields_upload UNION ALL SELECT destination FROM tbl_fields_uniqueupload ORDER BY destination ASC");
			} else {
				$destinations = Symphony::Database()->fetch("SELECT destination FROM tbl_fields_upload ORDER BY destination ASC");
			}

			function remove_duplicates(array $array){
				$tmp_array = array();
				foreach($array as $key => $val) {
					if (!in_array($val, $tmp_array)) $tmp_array[$key]  = $val;
				}

				return $tmp_array;
			}

			/* FIND THE RIGHT PERMISSIONS FOR THE ENVIRONMENT */
			$fstat = $dstat = array('uid' => 0, 'gid' => 0);
			// Create test directory/file and get information on each
			try{
				mkdir('test');
				$dstat = stat('test');
				
				// Get information about newly created file
				if (file_put_contents('test/test.txt', 'test')) {
					$fstat = stat('test/test.txt');
					unlink('test/test.txt');
				}
				
				// Cleanup
				rmdir('test');
			} 
			// If directory creation fails, catch the error and alert user
			catch (Exception $e) {
				Administration::instance()->Page->pageAlert(
					__('Exception caught: Health Check could not create a test directory to determine your server permission requirements. Your recommended permissions will default to Symphony recommendations'),
					Alert::ERROR
				);
			}	
			// Get information about FTP uploaded directory/file
			$ftpdstat = stat('symphony');
			$ftpfstat = stat('index.php');
			
			if(is_array($ftpdstat) && is_array($ftpfstat)) {
				$result = array();
				if ($ftpfstat['uid'] == $fstat['uid']) {
					// If user IDs match
					$result['file'] = '0644';
				} else if ($ftpfstat['gid'] == $fstat['gid']) {
					// If group IDs match
					$result['file'] = '0664';
				} else if (isset($fstat['mode'])) {
					$result['file'] = substr(decoct($fstat['mode']), -3);
				} else {
					// Everything failed, so return "default" defaults.
					$result['file'] = '0644';
				}
		
				if ($ftpdstat['uid'] == $dstat['uid']) {
					// If user IDs match
					$result['directory'] = '0755';
				} else if ($ftpdstat['gid'] == $dstat['gid']) {
					// If group IDs match
					$result['directory'] = '0775';
				} else if (isset($dstat['mode'])) {
					$result['directory'] = substr(decoct($dstat['mode']), -3);
				} else {
					// Everything failed, so return "default" defaults.
					$result['directory'] = '0755';
				}
			} else {
				Administration::instance()->Page->pageAlert(
					__('Exception caught: Health Check could not test against Symphony core files. Your recommended permissions will default to Symphony recommendations'),
					Alert::ERROR
				);
				
				$result['file'] = '0644';
				$result['directory'] = '0755';
			}

			$div = new XMLElement('div');
			$table = new XMLElement('table');
			
			$fetchXmlimporter = ExtensionManager::fetchStatus(array('handle' => 'xmlimporter'));

			$directory = array('/manifest/cache','/manifest/tmp','/manifest/config.php','/workspace/data-sources/','/workspace/events/');
			if($fetchXmlimporter[0] == EXTENSION_ENABLED) $directory[] =  '/workspace/xml-importers';
			foreach(remove_duplicates($destinations) as $destination) $directory[] = $destination['destination'];

		   	foreach($directory as $dir) {
				$d = getcwd() . __($dir);
				
				if(is_dir($d) == true || $dir == '/manifest/config.php') {
					$permissions = substr(sprintf("%o", fileperms($d)), -4);
					$td_directory = Widget::TableData(General::sanitize(__($dir)));
					$td_permissions = Widget::TableData(General::sanitize($permissions));
					if(is_dir($d)) {
						if($permissions != $result['directory']) {
							$table->appendChild(Widget::TableRow(array($td_directory, $td_permissions),'hc-invalid'));
						} else {
							$table->appendChild(Widget::TableRow(array($td_directory, $td_permissions)));
						}
					}
					// File check to recommend correct permissions
					if(is_file($d)) {
						if($permissions != $result['file']) {
							$table->appendChild(Widget::TableRow(array($td_directory, $td_permissions),'hc-invalid'));
						} else {
							$table->appendChild(Widget::TableRow(array($td_directory, $td_permissions)));
						}
					}
				} else {
					$td_directory = Widget::TableData(General::sanitize(__($dir)));
					$td_permissions = Widget::TableData(General::sanitize(__('WARNING: This directory does not exist.')));
					$table->appendChild(Widget::TableRow(array($td_directory, $td_permissions),'hc-invalid'));
				}
			}
			
			$button = new XMLElement('a', 'Go to directory page', array('class' => 'button hc-button', 'href' => SYMPHONY_URL . '/extension/health_check/directories/'));
		   
			$div->appendChild($table);
			$div->appendChild($button);

			$context['panel']->appendChild($div);
		}
	}
?>