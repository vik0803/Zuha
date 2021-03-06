<?php
/**
 * Webpages Controller
 *
 * Used to set variables used in the view files for the webpage plugin. 
 *
 * PHP versions 5
 *
 * Zuha(tm) : Business Management Applications (http://zuha.com)
 * Copyright 2009-2012, Zuha Foundation Inc. (http://zuha.org)
 *
 * Licensed under GPL v3 License
 * Must retain the above copyright notice and release modifications publicly.
 *
 * @copyright     Copyright 2009-2012, Zuha Foundation Inc. (http://zuha.com)
 * @link          http://zuha.com Zuha™ Project
 * @package       zuha
 * @subpackage    zuha.app.plugins.webpages.controllers
 * @since         Zuha(tm) v 0.0.1
 * @license       GPL v3 License (http://www.gnu.org/licenses/gpl.html) and Future Versions
 */
class AppWebpagesController extends WebpagesAppController {

/**
 * Name
 * 
 * @var string
 */
	public $name = 'Webpages';

/**
 * Uses
 * 
 * @var string
 */
    public $uses = array('Webpages.Webpage', 'File');

/**
 * Paginate
 * 
 * @var array
 */
	public $paginate = array('limit' => 10, 'order' => array('Webpage.created' => 'desc'));
	
    // public $components = array('Comments.Comments' => array('userModelClass' => 'User'));

/**
 * Helpers
 * 
 * @var array 
 */
    public $helpers = array('Cke');  // do we need this here anymre?? 7/27/13 RK
	
		
/**
 * Before filter
 * 
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		// $this->passedArgs['comment_view_type'] = 'flat';  11/11/RK 
		
		if (in_array('Drafts', CakePlugin::loaded()) && !empty($this->request->params['named']['draft'])) { 
			$this->Webpage->Behaviors->attach('Drafts.Draftable', array('returnVersion' => $this->request->params['named']['draft']));
		}
	}

/**
 * Index method
 *
 * @param string $type content|section|template|sub
 * @param string|int UUID or Int
 * @return void
 */
	public function index($type = 'content', $id = null) {
        $index = method_exists($this, '_index' . ucfirst($type)) ? '_index' . ucfirst($type) : '_indexContent';
        return $this->$index($type, $id);
	}
	
/**
 * Index of type Content
 * 
 * @param type $id
 * @return void
 */
    protected function _indexContent($type) {
		$this->paginate['conditions']['Webpage.type'] = $type;
		$this->paginate['conditions']['AND']['OR'][]['Webpage.parent_id'] = 0;
		$this->paginate['conditions']['AND']['OR'][]['Webpage.parent_id'] = null;
		$this->paginate['order']['Webpage.parent_id'] = 'DESC';
		//$this->paginate['conditions'][] = 'Webpage.lft + 1 =  Webpage.rght'; // find leaf nodes (childless parents) only
		$this->paginate['contain'][] = 'Child';
		//$this->paginate['fields'] = array('id', 'name', 'content', 'modified');
		$this->set('webpages', $webpages = $this->paginate());
		$this->set('sections', $this->Webpage->find('all', array('conditions' => array('Webpage.parent_id NOT' => 0), 'group' => 'Webpage.parent_id', 'contain' => array('Parent'))));
		$this->set('displayName', 'title');
		$this->set('displayDescription', 'content'); 
		$this->set('page_title_for_layout', 'Pages');
		$this->set('page_types', $this->Webpage->types());
		$this->view = $this->_fileExistsCheck('index_' . $type . $this->ext) ? 'index_' . $type : 'index_content';
		return $webpages;
    }
	
/**
 * Index of type Section
 * 
 * @param type $id
 * @return void
 */
    protected function _indexSection($type, $id) {
		$this->paginate['conditions']['Webpage.parent_id'] = $id;
		//$this->paginate['conditions'][] = 'Webpage.lft + 1 =  Webpage.rght'; // find leaf nodes (childless parents) only
		//$this->paginate['fields'] = array('id', 'name', 'content', 'modified');
		$this->set('webpage', $webpage = $this->Webpage->find('first', array('conditions' => array('Webpage.id' => $id))));
		$this->set('webpages', $webpages = $this->paginate());
		$this->set('sections', $this->Webpage->find('all', array('conditions' => array('Webpage.parent_id NOT' => 0), 'group' => 'Webpage.parent_id', 'contain' => array('Parent'))));
		$this->set('displayName', 'title');
		$this->set('page_title_for_layout', __('%s Section', $webpage['Webpage']['name']));
		$this->view = 'index_section';
		return $webpages;
    }
    
/**
 * Index of type Sub of Content
 * 
 * @param type $id
 * @return void
 * @throws NotFoundException
 */
    protected function _indexSub() {
		$this->paginate['conditions']['Webpage.type'] = 'content';
		//$this->paginate['fields'] = array('Webpage.id', 'Webpage.name', 'Webpage.content', 'Webpage.modified', 'Parent.id', 'Parent.name');
		$this->paginate['contain'] = array('Parent');
        $webpages = $this->paginate();
		$this->set(compact('webpages'));
		$this->set('displayName', 'title');
		$this->set('displayDescription', 'content'); 
		$this->set('page_title_for_layout', __('%s <small>Section</small>', $webpages[0]['Parent']['name']));
		$this->layout = 'default';	
		$this->view = 'index_sub';
    }
    
/**
 * Index of type Element
 * 
 * @param type $id
 * @return void
 * @throws NotFoundException
 */
    protected function _indexElement() {
		$this->paginate['conditions']['Webpage.type'] = 'element';
		$this->paginate['fields'] = array('id', 'name', 'content', 'modified');
		$this->set('webpages', $this->paginate());
        
		$this->set('displayName', 'title');
		$this->set('displayDescription', 'content'); 
		$this->set('page_title_for_layout', 'Widgets / Elements');
		$this->layout = 'default';
		$this->view = 'index_element';
    }


    protected function _indexEmail() {
		$this->paginate['conditions']['Webpage.type'] = 'email';
		$this->set('webpages', $this->paginate());
		$this->set('displayName', 'title');
		$this->set('displayDescription', 'content');
		$this->set('page_title_for_layout', 'Email Templates');
		$this->layout = 'default';
		$this->view = 'index_email';
    }
    
/**
 * Index pf type Template
 * 
 * @param type $id
 * @return void
 * @throws NotFoundException
 */
    protected function _indexTemplate() {
    	$this->redirect('admin');
		$this->paginate['conditions']['Webpage.type'] = 'template';
		$this->set('webpages', $this->paginate());
        
		App::uses('Template', 'Model');
		$Template = new Template;
		$this->set('templates', $templates = $Template->find('all', array('conditions' => array('Template.install NOT' => null))));
		$sync = $this->Webpage->syncFiles('template');
		$this->view = 'index_template';
		$this->set('page_title_for_layout', 'Site Templates');
		$this->set('title_for_layout', 'Site Templates');
    }
    
    

/**
 * View method
 *
 * @param string
 * @return void
 */
	public function view($id = null) {
		$this->Webpage->id = $id;
		if (!$this->Webpage->exists()) {
			throw new NotFoundException(__('Page not found'));
		}
		
		$page = $webpage = $this->Webpage->find("first", array(
		    "conditions" => array(
                'Webpage.id' => $id
                ),
		    'contain' => array(
				'Child'
				)
		    ));
		
		if ($webpage['Webpage']['type'] == 'template') {
			// do nothing, we don't need to parse template pages, because if we're viewing a template page we want to see the template tags
		} else {
			$this->Webpage->parseIncludedPages ($webpage, null, null, $this->userRoleId, $this->request);
			$webpage['Webpage']['content'] = $this->userRoleId == 1 ? '<div id="webpage'.$id.'" class="edit-box" pageid="'.$id.'">'.$webpage['Webpage']['content'].'</div>' : $webpage['Webpage']['content'];
		}

		if (!empty($this->request->params['requested'])) {
            return $webpage['Webpage']['content'];
        }

		if ($_SERVER['REDIRECT_URL'] == '/app/webroot/error') {
			$webpage = $this->Webpage->handleError($webpage, $this->request);
		}
		$this->set(compact('webpage'));
		$this->set('page_title_for_layout', $webpage['Webpage']['name']);
        $this->set('page', $page['Webpage']['content']); // an unparsed version of the page for the inline editor
       	$this->view = $this->_fileExistsCheck('view_' . $page['Webpage']['type'] . $this->ext) ? 'view_' . $page['Webpage']['type'] : 'view_content';
	}

/**
 * Add method
 *
 * @param string
 * @return void
 */
	public function add($type = 'content', $parentId = NULL) {
		$this->redirect('admin');
		$this->type = $type;
		if ($this->request->is('post')) {
			try {
				$this->Webpage->saveAll($this->request->data);
				$this->Session->setFlash(__('Saved'), 'flash_success');
				$redirect = !empty($this->request->data['Alias']['name']) ? __('/%s', $this->request->data['Alias']['name']) : array('action' => 'view', $this->Webpage->id);
				$this->redirect($redirect);
			} catch(Exception $e) {
				$this->Session->setFlash($e->getMessage(), 'flash_warning');
			}
		}
		if (!$this->Webpage->types($type)) {
			throw new NotFoundException(__('Invalid content type'));
		}
		$this->request->data['Webpage']['type'] = $type;
        $add = method_exists($this, '_add' . ucfirst($type)) ? '_add' . ucfirst($type) : '_addContent';
        $this->$add($parentId);
	}
	
    
/**
 * add content page
 */
    protected function _addContent() {
		$this->request->data['Alias']['name'] = !empty($this->request->params['named']['alias']) ? rtrim(str_replace('+', '/', $this->request->params['named']['alias']), '/') : null;
		// auto add to a webpage menu
		App::uses('WebpageMenu', 'Webpages.Model');
		$WebpageMenu = new WebpageMenu();
		$this->set('parents', $WebpageMenu->generateTreeList(null, null, null, ' - - '));
		$this->set('menus', $WebpageMenu->find('list', array('fields' => array('WebpageMenu.code', 'WebpageMenu.name'), 'conditions' => array('WebpageMenu.parent_id' => null))));
		// reuquired to have per page permissions
		$this->set('userRoles', $this->Webpage->Creator->UserRole->find('list'));
		$this->set('page_title_for_layout', __('Page Builder'));
		$this->view = $this->_fileExistsCheck('add_' . $this->type . $this->ext) ? 'add_' . $this->type : 'add_content';       
    }
	
/**
 * add sub page
 * 
 * @param string $parentId
 */
    protected function _addSub($parentId) {
		//Set Parent Properties if parentID is given else creat a new Page Type
		
		$parent = $this->Webpage->find('first', array('conditions' => array('Webpage.id' => $parentId), 'contain' => array('Child')));
		$this->request->data['Alias']['name'] = !empty($parent['Alias']['name']) ? $parent['Alias']['name'] . '/' : null;
		$this->set('userRoles', $this->Webpage->Creator->UserRole->find('list'));
		$this->set('parent', $parent);
		$this->set('page_title_for_layout', __('<small>Create a subpage of</small> %s', $parent['Webpage']['name']));
		$this->view = 'add_sub';      
    }
    
/**
 * add element
 */
    protected function _addElement() {
		// reuquired to have per page permissions
		$this->set('userRoles', $this->Webpage->Creator->UserRole->find('list'));
		$this->set('page_title_for_layout', __('Widget/Element Builder'));
		$this->view = 'add_element';        
    }
    
/**
 * add template
 */
    protected function _addTemplate() {
        $this->set('userRoles', $this->Webpage->Creator->UserRole->find('list'));
		$this->set('page_title_for_layout', __('Template Builder'));
		$this->view = 'add_template';        
    }

    protected function _addEmail() {
        $this->set('userRoles', $this->Webpage->Creator->UserRole->find('list'));
		$this->set('page_title_for_layout', __('Email Builder'));
		$this->view = 'add_email';
    }

/**
 * Edit method
 * 
 * @param string
 * @return void
 */
	public function edit($id = null) {
        $this->Webpage->id = $id;
		if (!$this->Webpage->exists()) {
			throw new NotFoundException(__('Page not found'));
		}
		if (!empty($this->request->data)) {
			try {
				$this->Webpage->saveAll($this->request->data);
				$this->Session->setFlash(__('Saved'), 'flash_success');
				$this->request->data['Webpage']['type'] == 'template' ? null : $this->redirect(array('action' => 'view', $this->Webpage->id));
			} catch(Exception $e) {
				$this->Session->setFlash($e->getMessage(), 'flash_warning');
			}
		}
		
		$templates = $this->Webpage->syncFiles('template');
		$this->request->data = $this->Webpage->find('first', array('conditions' => array('Webpage.id' => $id), 'contain' => array('Child', 'Alias')));
		$this->request->data = $this->Webpage->cleanOutputData($this->request->data);
		
		// required to have per page permissions
		$userRoles = $this->Webpage->Creator->UserRole->find('list');
		
		$types = $this->Webpage->types();

		// don't believe this is used 03/23/2014 RK
		// if ($this->request->data['Webpage']['type'] == 'template') {
			// if (defined('__WEBPAGES_DEFAULT_CSS_FILENAMES')) {
				// $cssFiles = unserialize(__WEBPAGES_DEFAULT_CSS_FILENAMES);
				// $cssFile = $cssFiles['all'][0];
			// } else {
				// $cssFile = 'screen';
			// }
			// $this->set('ckeSettings', array(
				// 'contentsCss' => '/theme/default/css/'.$cssFile.'.css',
				// 'buttons' => array('Source')
				// ));
		// } else {
			// unset($userRoles[1]);
			// $this->set('ckeSettings', null);
		// }
		// 1/6/2012 rk - $this->set('templateUrls', $this->Webpage->templateUrls($this->request->data));
		
		// these three lines should be for content only
		App::uses('WebpageMenu', 'Webpages.Model');
		$WebpageMenu = new WebpageMenu();
		$this->set('menus', $WebpageMenu->find('list', array('fields' => array('WebpageMenu.code', 'WebpageMenu.name'), 'conditions' => array('WebpageMenu.parent_id' => null))));
		
		$this->set('page_title_for_layout', __('%s Editor', Inflector::humanize($this->Webpage->types[$this->request->data['Webpage']['type']])));
		$this->set(compact('userRoles', 'types'));
		$type = $this->request->data['Webpage']['type'];
		$this->view = $this->_fileExistsCheck('edit_' . $type . $this->ext) ? 'edit_' . $type : 'edit_content';
        $this->layout = 'default';
	}
	
/**
 * Delete method
 * 
 * @param string
 * @return void
 */
	public function delete($id = null) {
		$this->Webpage->id = $id;
		if (!$this->Webpage->exists()) {
			throw new NotFoundException(__('Page not found'));
		}
		if ($this->Webpage->delete($id, true)) {
			$this->Session->setFlash(__('Webpage deleted', true), 'flash_success');
			$this->redirect(array('action'=>'index'));
		} else {
			$this->Session->setFlash(__('Webpage could not be deleted.', true), 'flash_warning');
			$this->redirect(array('action'=>'index'));
		}
	}
	
/**
 * Save method
 * 
 * Special use case for saving content, widgets, and templates via the admin navbar.  
 * 
 * @param mixed $id - String or integer
 * @return void - Always redirect to the referring page
 */
	public function save($id = null) {
        if (empty($id) && !empty($this->request->data['Webpage']['url'])) {
            if (!empty($this->request->data['Webpage']['id']) && $this->Webpage->updateTemplateSettings($this->request->data)) {
                $this->Session->setFlash(__('Template applied'), 'flash_success');
                $this->redirect($this->referer());
            } else {
                $this->Session->setFlash(__('Template is already applied, or could not be applied.'), 'flash_warning');
                $this->redirect($this->referer());
            }
        } else {
            // saves a regular content or widget page
    		$this->render(false);
    		$msg   = "";
    		$err   = false;
    		$pageContent=  $this->request->data;
    		$this->request->data = $this->Webpage->read(null, $id);
    		if (!empty($this->request->data)) {
    			$this->request->data['Webpage']['content'] = $pageContent;
    			if ($this->Webpage->save($this->request->data)) {
    				$msg = __('Page %s saved', $this->request->data['Webpage']['id']);
    			} else {
    				$err = true;
    				$msg = __('Cannot save page id #%s', $this->request->data['Webpage']['id']);
    			}
    		} else {
    			$err = true;
    			$msg = __('Page %s not found', $this->request->data['Webpage']['id']);
    		}
    		if($this->RequestHandler->isAjax()) {
    			$this->autoRender = $this->layout = false;
    			echo json_encode(array('msg' => $msg));
    			exit;
    		}
        }
	}

/**
 * Export method
 * 
 * Export a template to an array that can be used in the templates table
 * 
 * @param int $id
 */
 	public function export($id) {
 		try {
 			$data = $form = $this->Webpage->export($id);
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect($this->referer());
		}
		$form['Template']['install'] = substr($form['Template']['install'], 0, 500);
		if (!unserialize($data['Template']['install'])) {
			debug('data not serialized properly');
			exit;
		}
		
 		if ($this->request->is('post') || $this->request->is('push')) {
 			if (!unserialize($data['Template']['install'])) {
 				debug('serialization error');
				debug($data['Template']['install']);
				exit;
 			}
			$this->request->data['Template']['install'] = $data['Template']['install'];
			debug('Copy and paste this into the app/Config/Schema/schema.php file in the appropriate spot.');
			debug($this->request->data);
			exit;
 		}
		$this->request->data = $form;
		$this->set('page_title_for_layout', __('Export %s', $this->request->data['Template']['layout']));
		$this->set('title_for_layout', __('Export %s', $this->request->data['Template']['layout']));
 	}

 
 /**
  * Convience Function checks if files exists in sites pat.
  * or in App path this could be moved to the AppController
  * 
  * Probably a better way to do this. 
  * 
  * @param string
  * @return bool
  */
 
	protected function _fileExistsCheck($filename) {
		App::uses('File', 'Utility');
		$return = false;
		if(isset($filename)) {
		 	$path = ROOT . '/' . SITE_DIR . '/Locale/Plugin/' . $this->plugin . '/View/' . $this->viewPath . '/';
			$file = new File($path . $filename);
			$return =  $file->exists();
		}
		if($return == false) {
		 	$path = App::pluginPath($this->plugin) . '/View/' . $this->viewPath . '/';
			$file = new File($path . $filename);
			$return = $file->exists();
		}
	 	return $return;
	}

}

if(!isset($refuseInit)) {
	class WebpagesController extends AppWebpagesController {}
}
