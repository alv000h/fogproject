<?php
class UserManagementPage extends FOGPage
{
    public $node = 'user';
    public $name = 'User Management';
    public function __construct($name = '')
    {
        parent::__construct($this->name);
        if ($_REQUEST['id']) {
            $this->subMenu = array(
                $this->linkformat => self::$foglang['General'],
                $this->delformat => self::$foglang['Delete'],
            );
            $this->notes = array(
                self::$foglang['User'] => $this->obj->get('name'),
            );
        }
        self::$HookManager->processEvent('SUB_MENULINK_DATA', array('menu'=>&$this->menu, 'submenu'=>&$this->subMenu, 'id'=>&$this->id, 'notes'=>&$this->notes, 'object'=>&$this->obj, 'linkformat'=>&$this->linkformat, 'delformat'=>&$this->delformat));
        $this->headerData = array(
            '<input type="checkbox" name="toggle-checkbox" class="toggle-checkboxAction" />',
            _('Username'),
            _('Edit')
        );
        $this->templates = array(
            '<input type="checkbox" name="user[]" value="${id}" class="toggle-action" />',
            sprintf('<a href="?node=%s&sub=edit&%s=${id}" title="%s">${name}</a>', $this->node, $this->id, _('Edit User')),
            sprintf('<a href="?node=%s&sub=edit&%s=${id}" title="%s"><i class="icon fa fa-pencil"></i></a>', $this->node, $this->id, _('Edit User'))
        );
        $this->attributes = array(
            array('class'=>'l filter-false','width'=>16),
            array(),
            array('class'=>'c filter-false','width'=>55),
        );
        $types = array();
        self::$HookManager->processEvent(
            'USER_TYPES_FILTER',
            array('types' => &$types)
        );
        self::$returnData = function (&$User) use (&$types) {
            if (!$User->isValid()) {
                return;
            }
            if (count($types) > 0
                && in_array($User->get('type'), $types)
            ) {
                return;
            }
            $this->data[] = array(
                'id' => $User->get('id'),
                'name' => $User->get('name'),
            );
            unset($User);
        };
    }
    public function add()
    {
        $this->title = _('New User');
        unset($this->headerData);
        $this->templates = array(
            '${field}',
            '${input}',
        );
        $this->attributes = array(
            array(),
            array(),
        );
        $this->data = array();
        $fields = array(
            '<input style="display:none" type="text" name="fakeusernameremembered"/>'=>'<input style="display:none" type="password" name="fakepasswordremembered"/>',
            _('User Name') => sprintf('<input type="text" class="username-input" name="name" value="%s" autocomplete="off"/>', $_REQUEST['name']),
            _('User Password') => '<input type="password" class="password-input1" name="password" value="" autocomplete="off" id="password"/>',
            _('User Password (confirm)') => '<input type="password" class="password-input2" name="password_confirm" value="" autocomplete="off"/>',
            sprintf('%s&nbsp;<i class="icon icon-help hand fa fa-question" title="%s"></i>', _('Mobile/Quick Image Access Only?'), _('Warning - if you tick this box, this user will not be able to log into this FOG Management Console in the future.')) => '<input type="checkbox" name="isGuest" autocomplete="off"/>',
            '&nbsp;' => sprintf('<input name="add" type="submit" value="%s"/>', _('Create User')),
        );
        array_walk($fields, $this->fieldsToData);
        self::$HookManager->processEvent('USER_ADD', array('data'=>&$this->data, 'templates'=>&$this->templates, 'attributes'=>&$this->attributes));
        printf('<h2>%s</h2><form method="post" action="%s">', _('Add new user account'), $this->formAction);
        $this->render();
        echo '</form>';
    }
    public function addPost()
    {
        self::$HookManager->processEvent('USER_ADD_POST');
        try {
            $name = strtolower(trim($_REQUEST['name']));
            if (self::getClass('UserManager')->exists($name)) {
                throw new Exception(_('Username already exists'));
            }
            $User = self::getClass('User')
                ->set('name', $name)
                ->set('type', isset($_REQUEST['isGuest']))
                ->set('password', $_REQUEST['password']);
            if (!$User->save()) {
                throw new Exception(_('Failed to create user'));
            }
            self::$HookManager->processEvent('USER_ADD_SUCCESS', array('User'=>&$User));
            $this->setMessage(sprintf('%s<br/>%s', _('User created'), _('You may now create another')));
        } catch (Exception $e) {
            self::$HookManager->processEvent('USER_ADD_FAIL', array('User'=>&$User));
            $this->setMessage($e->getMessage());
        }
        unset($User);
        $this->redirect($this->formAction);
    }
    public function edit()
    {
        $this->title = sprintf('%s: %s', _('Edit'), $this->obj->get('name'));
        $fields = array(
            _('User Name') => sprintf('<input type="text" class="username-input" name="name" value="%s"/>', $this->obj->get('name')),
            _('New Password') => '<input type="password" class="password-input1" id="password" name="password" value=""/>',
            _('New Password (confirm)') => '<input type="password" class="password-input2" name="password_confirm" value=""/>',
            sprintf('%s&nbsp;<i class="icon icon-help hand fa fa-question" title="%s"></i>', _('Mobile/Quick Image Access Only?'), _('Warning - if you tick this box, this user will not be able to log into this FOG Management Console in the future.')) => sprintf('<input type="checkbox" name="isGuest" autocomplete="off"%s/>', ($this->obj->get('type') == 1 ? ' checked' : '')),
            '&nbsp;' => sprintf('<input name="update" type="submit" value="%s"/>', _('Update')),
        );
        unset($this->headerData);
        $this->templates = array(
            '${field}',
            '${input}',
        );
        $this->attributes = array(
            array(),
            array(),
        );
        $this->data = array();
        array_walk($fields, $this->fieldsToData);
        self::$HookManager->processEvent('USER_EDIT', array('data'=>&$this->data, 'templates'=>&$this->templates, 'attributes'=>&$this->attributes));
        printf('<form method="post" action="%s">', $this->formAction);
        $this->render();
        echo '</form>';
    }
    public function editPost()
    {
        self::$HookManager->processEvent('USER_EDIT_POST', array('User'=>&$this->obj));
        try {
            $name = strtolower(trim($_REQUEST['name']));
            if ($name != trim($this->obj->get('name')) && $this->obj->getManager()->exists($name, $this->obj->get('id'))) {
                throw new Exception(_('Username already exists'));
            }
            $this->obj
                ->set('name', $name)
                ->set('type', isset($_REQUEST['isGuest']))
                ->set('password', $_REQUEST['password']);
            if (!$this->obj->save()) {
                throw new Exception(_('User update failed'));
            }
            self::$HookManager->processEvent('USER_UPDATE_SUCCESS', array('User'=>&$this->obj));
            $this->setMessage(_('User updated'));
        } catch (Exception $e) {
            self::$HookManager->processEvent('USER_UPDATE_FAIL', array('User'=>&$this->obj));
            $this->setMessage($e->getMessage());
        }
        $this->redirect($this->formAction);
    }
}
