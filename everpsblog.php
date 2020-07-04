<?php
/**
 * Project : everpsblog
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogPost.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCategory.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogTag.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogComment.php';
require_once _PS_MODULE_DIR_.'everpsblog/classes/EverPsBlogCleaner.php';

class EverPsBlog extends Module
{
    private $html;
    private $postErrors = array();
    private $postSuccess = array();
    public static $route = array();

    public function __construct()
    {
        $this->name = 'everpsblog';
        $this->tab = 'front_office_features';
        $this->version = '2.2.5';
        $this->author = 'Team Ever';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ever Blog');
        $this->description = $this->l('Simply a blog :-)');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module ?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $id_shop = (int)$this->context->shop->id;
        $emptytrash = _PS_BASE_URL_._MODULE_DIR_.'everpsblog/emptytrash.php?token=';
        $pending = _PS_BASE_URL_._MODULE_DIR_.'everpsblog/pending.php?token=';
        $planned = _PS_BASE_URL_._MODULE_DIR_.'everpsblog/planned.php?token=';
        $blogToken = Tools::substr(Tools::encrypt('everpsblog/cron'), 0, 10);
        $this->context->smarty->assign(array(
            'image_dir' => $this->_path.'views/img',
            'everpsblogcron' => $emptytrash.$blogToken.'&id_shop='.(int)$id_shop,
            'everpsblogcronpending' => $pending.$blogToken.'&id_shop='.(int)$id_shop,
            'everpsblogcronplanned' => $planned.$blogToken.'&id_shop='.(int)$id_shop,
        ));
    }

    public function install()
    {
        // Install SQL
        include(dirname(__FILE__).'/sql/install.php');
        // Creating root category
        $root_category = new EverPsBlogCategory();
        $root_category->is_root_category = 1;
        $root_category->active = 1;
        $root_category->id_shop = (int)$this->context->shop->id;
        foreach (Language::getLanguages(false) as $language) {
            $root_category->title[$language['id_lang']] = 'Root';
            $root_category->content[$language['id_lang']] = 'Root';
        }
        $root_category->save();
        // Install
        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('displayRightColumn')
            && $this->registerHook('displayFooterProduct')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayCustomerAccount')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('overrideLayoutTemplate')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('backofficeHeader')
            && $this->registerHook('actionObjectEverPsBlogPostDeleteAfter')
            && $this->registerHook('actionObjectEverPsBlogCategoryDeleteAfter')
            && $this->registerHook('actionObjectEverPsBlogTagDeleteAfter')
            && $this->registerHook('actionObjectEverPsBlogCommentDeleteAfter')
            && $this->installModuleTab('AdminEverPsBlog', 'IMPROVE', 'Blog')
            && $this->installModuleTab('AdminEverPsBlogPost', 'AdminEverPsBlog', 'Posts')
            && $this->installModuleTab('AdminEverPsBlogCategory', 'AdminEverPsBlog', 'Categories')
            && $this->installModuleTab('AdminEverPsBlogTag', 'AdminEverPsBlog', 'Tags')
            && $this->installModuleTab('AdminEverPsBlogComment', 'AdminEverPsBlog', 'Comments')
            && Configuration::updateValue('EVERBLOG_ADMIN_EMAIL', 1)
            && Configuration::updateValue('EVERBLOG_EMPTY_TRASH', 7)
            && Configuration::updateValue('EVERBLOG_ALLOW_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_CHECK_COMMENTS', 1)
            && Configuration::updateValue('EVERBLOG_BANNED_USERS', '')
            && Configuration::updateValue('EVERBLOG_BANNED_IP', '')
            && Configuration::updateValue('EVERPSBLOG_PAGINATION', '10')
            && Configuration::updateValue('EVERPSBLOG_HOME_NBR', '4')
            && Configuration::updateValue('EVERPSBLOG_PRODUCT_NBR', '4');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        return $this->uninstallModuleTab('AdminEverPsBlog')
            && parent::uninstall();
    }

    private function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        if ($tabClass == 'AdminEverPsBlog' && $this->isSeven) {
            $tab->icon = 'icon-team-ever';
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = $tabName;
        }

        return $tab->add();
    }

    private function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int)Tab::getIdFromClassName($tabClass));

        return $tab->delete();
    }

    /**
     * Add link rewrite rule
     * @see https://stackoverflow.com/questions/49430883/creating-a-url-rewrite-module-in-prestashop
     */
    public function hookModuleRoutes($params)
    {
//         if (Tools::getValue('controller') == 'AdminModules') {
//             return array();
//         }

        $base_route = Configuration::get('EVERPSBLOG_ROUTE') ? Configuration::get('EVERPSBLOG_ROUTE') : 'blog';

        return array(
            'module-everpsblog-blog' => array(
                'controller' => 'blog',
                'rule' => $base_route,
                'keywords' => array(
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                    'controller' => 'blog',
                )
            ),
            'module-everpsblog-category' => array(
                'controller' => 'category',
                'rule' => $base_route.'/category{/:id_ever_category}-{:link_rewrite}',
                'keywords' => array(
                    'id_ever_category' => array('regexp' => '[0-9]+', 'param' => 'id_ever_category'),
                    'link_rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                )
            ),
            'module-everpsblog-post' => array(
                'controller' => 'post',
                'rule' => $base_route.'/post{/:id_ever_post}-{:link_rewrite}',
                'keywords' => array(
                    'id_ever_post' => array('regexp' => '[0-9]+', 'param' => 'id_ever_post'),
                    'link_rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                )
            ),
            'module-everpsblog-tag' => array(
                'controller' => 'tag',
                'rule' => $base_route.'/tag{/:id_ever_tag}-{:link_rewrite}',
                'keywords' => array(
                    'id_ever_tag' => array('regexp' => '[0-9]+', 'param' => 'id_ever_tag'),
                    'link_rewrite' => array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'everpsblog',
                )
            )
        );
    }

    public function getContent()
    {
        $this->html = '';

        if (Tools::isSubmit('submitEverPsBlogConf')) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }

        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }

        $link = new Link();
        $this->context->smarty->assign(array(
            'blog_url' => $link->getModuleLink('everpsblog', 'blog'),
        ));

        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/footer.tpl');

        return $this->html;
    }

    public function postValidation()
    {
        if (Tools::isSubmit('submitEverPsBlogConf')) {
            if (!Tools::getValue('EVERPSBLOG_PAGINATION')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_PAGINATION'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts per page" is not valid '.Tools::getValue('EVERPSBLOG_PAGINATION'));
            }
            if (!Tools::getValue('EVERPSBLOG_HOME_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_HOME_NBR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts for home" is not valid '.Tools::getValue('EVERPSBLOG_HOME_NBR'));
            }
            if (!Tools::getValue('EVERPSBLOG_PRODUCT_NBR')
                && !Validate::isUnsignedInt(Tools::getValue('EVERPSBLOG_PRODUCT_NBR'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Posts for product" is not valid '.Tools::getValue('EVERPSBLOG_PRODUCT_NBR'));
            }
            if (!Tools::getIsset('EVERPSBLOG_ROUTE')
                || !Validate::isLinkRewrite(Tools::getValue('EVERPSBLOG_ROUTE'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Blog route" is not valid '.Tools::getValue('EVERPSBLOG_ROUTE'));
            }

            if (!Tools::getIsset('EVERBLOG_ADMIN_EMAIL')
                || !Validate::isUnsignedInt(Tools::getValue('EVERBLOG_ADMIN_EMAIL'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Admin email" is not valid '.Tools::getValue('EVERBLOG_ADMIN_EMAIL'));
            }

            if (!Tools::getIsset('EVERBLOG_ALLOW_COMMENTS')
                || !Validate::isBool(Tools::getValue('EVERBLOG_ALLOW_COMMENTS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Allow comments" is not valid');
            }

            if (!Tools::getIsset('EVERBLOG_CHECK_COMMENTS')
                || !Validate::isBool(Tools::getValue('EVERBLOG_CHECK_COMMENTS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Check comments" is not valid');
            }

            if (!Tools::getIsset('EVERBLOG_BANNED_USERS')
                || !Validate::isGenericName(Tools::getValue('EVERBLOG_BANNED_USERS'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Banned users" is not valid');
            }

            if (!Tools::getIsset('EVERBLOG_BANNED_IP')
                || !Validate::isGenericName(Tools::getValue('EVERBLOG_BANNED_IP'))
            ) {
                $this->postErrors[] = $this->l('Error : The field "Banned IP" is not valid');
            }
            if (Tools::getValue('EVERBLOG_FANCYBOX')
                && !Validate::isBool(Tools::getValue('EVERBLOG_FANCYBOX'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Fancybox" is not valid'
                );
            }
            foreach (Language::getLanguages(false) as $lang) {
                if (!Tools::getValue('EVERBLOG_TITLE_'.$lang['id_lang'])
                    || !Validate::isString(Tools::getValue('EVERBLOG_TITLE_'.$language['id_lang']))
                ) {
                    $this->postErrors[] = $this->l('Error : Blog title is invalid for lang ').$lang['id_lang'];
                }
                if (!Tools::getValue('EVERBLOG_META_DESC_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('EVERBLOG_META_DESC_'.$language['id_lang']))
                ) {
                    $this->postErrors[] = $this->l('Error : Blog meta description is invalid for lang ').$lang['id_lang'];
                }
                if (!Tools::getValue('EVERBLOG_TOP_TEXT_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('EVERBLOG_TOP_TEXT_'.$language['id_lang']))
                ) {
                    $this->postErrors[] = $this->l('Error : Blog top text is invalid for lang ').$lang['id_lang'];
                }
                if (!Tools::getValue('EVERBLOG_BOTTOM_'.$lang['id_lang'])
                    || !Validate::isCleanHtml(Tools::getValue('EVERBLOG_BOTTOM_'.$language['id_lang']))
                ) {
                    $this->postErrors[] = $this->l('Error : Blog bottom text is invalid for lang ').$lang['id_lang'];
                }
            }
        }
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        Configuration::deleteByName('PS_ROUTE_module-everpsblog-blog');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-category');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-post');
        Configuration::deleteByName('PS_ROUTE_module-everpsblog-tag');

        Hook::exec('hookModuleRoutes');
        $everblog_title = array();
        $everblog_meta_desc = array();
        $everblog_top_text = array();
        $everblog_bottom_text = array();
        foreach (Language::getLanguages(false) as $lang) {
            $everblog_title[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            ) : '';
            $everblog_meta_desc[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            ) : '';
            $everblog_top_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            ) : '';
            $everblog_bottom_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            ) : '';
        }
        foreach (array_keys($form_values) as $key) {
            if ($key == 'EVERBLOG_TITLE') {
                Configuration::updateValue(
                    $key,
                    $everblog_title
                );
            } elseif ($key == 'EVERBLOG_META_DESC') {
                Configuration::updateValue(
                    $key,
                    $everblog_meta_desc
                );
            } elseif ($key == 'EVERBLOG_TOP_TEXT') {
                Configuration::updateValue(
                    $key,
                    $everblog_top_text
                );
            } elseif ($key == 'EVERBLOG_BOTTOM_TEXT') {
                Configuration::updateValue(
                    $key,
                    $everblog_bottom_text
                );
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }

        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    protected function getConfigFormValues()
    {
        $formValues = array();
        $everblog_title = array();
        $everblog_meta_desc = array();
        $everblog_top_text = array();
        $everblog_bottom_text = array();
        foreach (Language::getLanguages(false) as $lang) {
            $everblog_title[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TITLE_'.$lang['id_lang']
            ) : '';
            $everblog_meta_desc[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_META_DESC_'.$lang['id_lang']
            ) : '';
            $everblog_top_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_TOP_TEXT_'.$lang['id_lang']
            ) : '';
            $everblog_top_text[$lang['id_lang']] = (Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            )) ? Tools::getValue(
                'EVERBLOG_BOTTOM_TEXT_'.$lang['id_lang']
            ) : '';
        }
        $formValues[] = array(
            'EVERPSBLOG_ROUTE' => Configuration::get('EVERPSBLOG_ROUTE'),
            'EVERPSBLOG_PAGINATION' => Configuration::get('EVERPSBLOG_PAGINATION'),
            'EVERPSBLOG_HOME_NBR' => Configuration::get('EVERPSBLOG_HOME_NBR'),
            'EVERPSBLOG_PRODUCT_NBR' => Configuration::get('EVERPSBLOG_PRODUCT_NBR'),
            'EVERBLOG_ADMIN_EMAIL' => Configuration::get('EVERBLOG_ADMIN_EMAIL'),
            'EVERBLOG_ALLOW_COMMENTS' => Configuration::get('EVERBLOG_ALLOW_COMMENTS'),
            'EVERBLOG_CHECK_COMMENTS' => Configuration::get('EVERBLOG_CHECK_COMMENTS'),
            'EVERBLOG_BANNED_USERS' => Configuration::get('EVERBLOG_BANNED_USERS'),
            'EVERBLOG_BANNED_IP' => Configuration::get('EVERBLOG_BANNED_IP'),
            'EVERBLOG_EMPTY_TRASH' => Configuration::get('EVERBLOG_EMPTY_TRASH'),
            'EVERBLOG_ANIMATE' => Configuration::get('EVERBLOG_ANIMATE'),
            'EVERBLOG_RELATED_POST' => Configuration::get('EVERBLOG_RELATED_POST'),
            'EVERBLOG_ARCHIVE_COLUMNS' => Configuration::get('EVERBLOG_ARCHIVE_COLUMNS'),
            'EVERBLOG_TAG_COLUMNS' => Configuration::get('EVERBLOG_TAG_COLUMNS'),
            'EVERBLOG_CATEG_COLUMNS' => Configuration::get('EVERBLOG_CATEG_COLUMNS'),
            'EVERBLOG_FANCYBOX' => Configuration::get('EVERBLOG_FANCYBOX'),
            'EVERBLOG_TITLE' => (!empty(
                $everblog_title[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_title : Configuration::getInt(
                'EVERBLOG_TITLE'
            ),
            'EVERBLOG_META_DESC' => (!empty(
                $everblog_meta_desc[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_meta_desc : Configuration::getInt(
                'EVERBLOG_META_DESC'
            ),
            'EVERBLOG_TOP_TEXT' => (!empty(
                $everblog_top_text[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_top_text : Configuration::getInt(
                'EVERBLOG_TOP_TEXT'
            ),
            'EVERBLOG_BOTTOM_TEXT' => (!empty(
                $everblog_bottom_text[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $everblog_bottom_text : Configuration::getInt(
                'EVERBLOG_BOTTOM_TEXT'
            ),

        );
        $values = call_user_func_array('array_merge', $formValues);
        // die(var_dump($values));
        return $values;
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEverPsBlogConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int)$this->context->language->id,
        );

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        // TODO : add default blog text per lang ?
        $employees = Employee::getEmployeesByProfile(
            1,
            true
        );
        $trash_days = array(
            array(
                'id_trash' => 0,
                'name' => $this->l('Do not empty trash')
            ),
            array(
                'id_trash' => 1,
                'name' => $this->l('One day')
            ),
            array(
                'id_trash' => 2,
                'name' => $this->l('Two days')
            ),
            array(
                'id_trash' => 3,
                'name' => $this->l('Three days')
            ),
            array(
                'id_trash' => 4,
                'name' => $this->l('Four days')
            ),
            array(
                'id_trash' => 5,
                'name' => $this->l('Five days')
            ),
            array(
                'id_trash' => 6,
                'name' => $this->l('Six days')
            ),
            array(
                'id_trash' => 7,
                'name' => $this->l('One week')
            ),
        );
        $form_fields = array();
        $form_fields[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Blog default Settings'),
                    'icon' => 'icon-download',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Blog base route'),
                        'name' => 'EVERPSBLOG_ROUTE',
                        'desc' => $this->l('Leaving empty will set "blog"'),
                        'hint' => $this->l('Use a keyword associated to your shop'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of posts for home'),
                        'name' => 'EVERPSBLOG_HOME_NBR',
                        'desc' => $this->l('Leaving empty will set 4 posts'),
                        'hint' => $this->l('Posts are 4 per row'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of posts for product'),
                        'name' => 'EVERPSBLOG_PRODUCT_NBR',
                        'desc' => $this->l('Leaving empty will set 4 posts'),
                        'hint' => $this->l('Posts are 4 per row'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Posts per page'),
                        'name' => 'EVERPSBLOG_PAGINATION',
                        'desc' => $this->l('Leaving empty will set 10 posts per page'),
                        'hint' => $this->l('Will add pagination'),
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Admin email'),
                        'desc' => $this->l('Will receive new comments notification by email'),
                        'hint' => $this->l('You can set a new account on your shop'),
                        'required' => true,
                        'name' => 'EVERBLOG_ADMIN_EMAIL',
                        'options' => array(
                            'query' => $employees,
                            'id' => 'id_employee',
                            'name' => 'email'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Allow comments on posts ?'),
                        'desc' => $this->l('Set yes to allow comments'),
                        'hint' => $this->l('You can check them before publishing'),
                        'required' => true,
                        'name' => 'EVERBLOG_ALLOW_COMMENTS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Check comments on posts before they are published ?'),
                        'desc' => $this->l('Set yes to check comments before publishing'),
                        'hint' => $this->l('In order to avoid spam'),
                        'required' => true,
                        'name' => 'EVERBLOG_CHECK_COMMENTS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Empty trash'),
                        'desc' => $this->l('Please choose auto empty trash in days'),
                        'hint' => $this->l('Will auto delete trashed posts on CRON task'),
                        'required' => true,
                        'name' => 'EVERBLOG_EMPTY_TRASH',
                        'options' => array(
                            'query' => $trash_days,
                            'id' => 'id_trash',
                            'name' => 'name',
                        ),
                        'lang' => false,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Default blog SEO title'),
                        'name' => 'EVERBLOG_TITLE',
                        'desc' => $this->l('Max 65 characters for SEO'),
                        'hint' => $this->l('Will impact SEO'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Default blog SEO meta description'),
                        'name' => 'EVERBLOG_META_DESC',
                        'desc' => $this->l('Max 165 characters for SEO'),
                        'hint' => $this->l('Will impact SEO'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Default blog top text'),
                        'name' => 'EVERBLOG_TOP_TEXT',
                        'desc' => $this->l('Will be shown on blog top default page'),
                        'hint' => $this->l('Explain your blog purpose'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                        'autoload_rte' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Default blog bottom text'),
                        'name' => 'EVERBLOG_BOTTOM_TEXT',
                        'desc' => $this->l('Will be shown on blog bottom default page'),
                        'hint' => $this->l('Explain your blog purpose'),
                        'cols' => 36,
                        'rows' => 4,
                        'lang' => true,
                        'autoload_rte' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Banned users'),
                        'name' => 'EVERBLOG_BANNED_USERS',
                        'desc' => $this->l('Add banned users typing their emails, one per line'),
                        'hint' => $this->l('Unwanted users won\'t be able to post comments'),
                        'cols' => 36,
                        'rows' => 4,
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Banned IP'),
                        'name' => 'EVERBLOG_BANNED_IP',
                        'desc' => $this->l('Add banned users typing their IP addresses, one per line'),
                        'hint' => $this->l('Unwanted users won\'t be able to post comments'),
                        'cols' => 36,
                        'rows' => 4,
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show parent categories list on left/right columns ?'),
                        'desc' => $this->l('Set yes show a list of all parent categories on left or right columns'),
                        'hint' => $this->l('Will show ordered parent categories on left/right columns'),
                        'name' => 'EVERBLOG_CATEG_COLUMNS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show tags list on left/right columns ?'),
                        'desc' => $this->l('Set yes to activate cool stuff'),
                        'hint' => $this->l('Set yes show a tags cloud on left or right columns'),
                        'required' => true,
                        'name' => 'EVERBLOG_TAG_COLUMNS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show archives list on left/right columns ?'),
                        'desc' => $this->l('Set yes show links for monthly posts on left or right columns'),
                        'hint' => $this->l('Will show yearly and monthly posts'),
                        'required' => true,
                        'name' => 'EVERBLOG_ARCHIVE_COLUMNS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show related posts on products pages ?'),
                        'desc' => $this->l('Set yes show related posts on product pages footer'),
                        'hint' => $this->l('Will show related posts on product page footer'),
                        'required' => true,
                        'name' => 'EVERBLOG_RELATED_POST',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate cool CSS animations ?'),
                        'desc' => $this->l('Set yes to activate cool stuff'),
                        'hint' => $this->l('Will add animations on posts, images, etc'),
                        'name' => 'EVERBLOG_ANIMATE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Fancybox'),
                        'hint' => $this->l('Set no if your theme already uses it'),
                        'desc' => $this->l('Use Fancybox for popups on post images'),
                        'name' => 'EVERBLOG_FANCYBOX',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'name' => 'submit',
                    'title' => $this->l('Save'),
                ),
            )
        );
        return $form_fields;
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addCss($this->_path.'views/css/ever.css');
    }

    public function hookBackofficeHeader()
    {
        return $this->hookActionAdminControllerSetMedia();
    }

    public function hookHeader()
    {
        $controller_name = Tools::getValue('controller');
        $module_name = Tools::getValue('module');
        if ($module_name == 'everpsblog') {
            $this->context->controller->addCSS(
                _PS_MODULE_DIR_.'everpsblog/views/css/everpsblog.css', 'all'
            );
            $this->context->controller->addJs(
                $this->_path.'views/js/everpsblog.js'
            );
            if ($controller_name == 'post') {
                if ((int)Configuration::get('EVERBLOG_FANCYBOX')) {
                    if ($controller_name != 'order') {
                        $this->context->controller->addCSS(($this->_path).'views/css/jquery.fancybox.min.css', 'all');
                        $this->context->controller->addJS(($this->_path).'views/js/jquery.fancybox.min.js', 'all');
                    }
                }
            }
        } else {
            $this->context->controller->addCSS(
                _PS_MODULE_DIR_.'everpsblog/views/css/everpsblog-columns.css', 'all'
            );
        }
        $this->context->controller->addCSS(
            _PS_MODULE_DIR_.'everpsblog/views/css/everpsblog-all.css', 'all'
        );
    }

    public function hookDisplayLeftColumn($params)
    {
        if ((int)Configuration::get('EVERPSBLOG_HOME_NBR')) {
            $post_number = (int)Configuration::get('EVERPSBLOG_HOME_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true
        );
        $tags = EverPsBlogTag::getAllTags(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $categories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $posts = EverPsBlogPost::getLatestPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            0,
            (int)$post_number
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $showArchives = Configuration::get(
            'EVERBLOG_ARCHIVE_COLUMNS'
        );
        $showCategories = Configuration::get(
            'EVERBLOG_CATEG_COLUMNS'
        );
        $showTags = Configuration::get(
            'EVERBLOG_TAG_COLUMNS'
        );
        $everpsblog = array();
        foreach ($posts as $post) {
            $post['title'] = EverPsBlogPost::changeShortcodes(
                $post['title'],
                (int)Context::getContext()->customer->id
            );
            $post['content'] = EverPsBlogPost::changeShortcodes(
                $post['content'],
                (int)Context::getContext()->customer->id
            );
            $everpsblog[] = $post;
        }
        $this->context->smarty->assign(array(
            'everpsblog' => $everpsblog,
            'showArchives' => $showArchives,
            'showCategories' => $showCategories,
            'showTags' => $showTags,
            'blogUrl' => $blogUrl,
            'tags' => $tags,
            'categories' => $categories,
            'animate' => $animate,
            'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/everpsblog/views/img/',
        ));
        return $this->display(__FILE__, 'views/templates/hook/columns.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayHome($params)
    {
        if ((int)Configuration::get('EVERPSBLOG_HOME_NBR')) {
            $post_number = (int)Configuration::get('EVERPSBLOG_HOME_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true
        );
        $posts = EverPsBlogPost::getLatestPosts(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            0,
            (int)$post_number
        );
        if (!$posts || !count($posts)) {
            return;
        }
        $evercategories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $everpsblog = array();
        foreach ($posts as $post) {
            $post['title'] = EverPsBlogPost::changeShortcodes(
                $post['title'],
                (int)Context::getContext()->customer->id
            );
            $post['content'] = EverPsBlogPost::changeShortcodes(
                $post['content'],
                (int)Context::getContext()->customer->id
            );
            $everpsblog[] = $post;
        }
        $this->context->smarty->assign(
            array(
                'blogUrl' => $blogUrl,
                'everpsblog' => $everpsblog,
                'evercategory' => $evercategories,
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/everpsblog/views/img/',
                'animated' => $animate,
            )
        );
        return $this->display(__FILE__, 'views/templates/hook/home.tpl');
    }

    public function hookDisplayCustomerAccount()
    {
        return $this->display(__FILE__, 'views/templates/hook/my-account.tpl');
    }

    public function hookDisplayMyAccountBlock($params)
    {
        return $this->hookDisplayCustomerAccount();
    }

    public function hookDisplayFooterProduct()
    {
        if ((int)Configuration::get('EVERPSBLOG_PRODUCT_NBR')) {
            $post_number = (int)Configuration::get('EVERPSBLOG_PRODUCT_NBR');
        } else {
            $post_number = 4;
        }
        $blogUrl = Context::getContext()->link->getModuleLink(
            $this->name,
            'blog',
            array(),
            true
        );
        $posts = EverPsBlogPost::getPostsByProduct(
            (int)$this->context->language->id,
            (int)$this->context->shop->id,
            (int)Tools::getValue('id_product'),
            1,
            (int)$post_number
        );
        if (!$posts
            || !count($posts)
        ) {
            return;
        }
        $evercategories = EverPsBlogCategory::getAllCategories(
            (int)$this->context->language->id,
            (int)$this->context->shop->id
        );
        $animate = Configuration::get(
            'EVERBLOG_ANIMATE'
        );
        $everpsblog = array();
        foreach ($posts as $post) {
            $post['title'] = EverPsBlogPost::changeShortcodes(
                $post['title'],
                (int)Context::getContext()->customer->id
            );
            $post['content'] = EverPsBlogPost::changeShortcodes(
                $post['content'],
                (int)Context::getContext()->customer->id
            );
            $everpsblog[] = $post;
        }
        $this->context->smarty->assign(
            array(
                'blogUrl' => $blogUrl,
                'everpsblog' => $everpsblog,
                'evercategory' => $evercategories,
                'default_lang' => (int)$this->context->language->id,
                'id_lang' => (int)$this->context->language->id,
                'blogImg_dir' => Tools::getHttpHost(true).__PS_BASE_URI__.'/modules/everpsblog/views/img/',
                'animated' => $animate,
            )
        );
        return $this->display(__FILE__, 'views/templates/hook/product.tpl');
    }

    public function hookDisplayFooter()
    {
        return $this->hookDisplayBeforeBodyClosingTag();
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        $controller_name = Tools::getValue('controller');
        $module_name = Tools::getValue('module');

        if ($module_name == 'everpsblog') {
            if ($controller_name == 'post') {
                $this->context->smarty->assign(
                    array(
                        'everfancybox' => (bool)Configuration::get('EVERBLOG_FANCYBOX'),
                    )
                );
                return $this->display(__FILE__, 'views/templates/hook/footer.tpl');
            }
        }
    }

    public function hookOverrideLayoutTemplate($params)
    {
        if (isset($params['controller']->page_name)
            && (
                $params['controller']->page_name == 'module-everpsblog-blog'
                || $params['controller']->page_name == 'module-everpsblog-category'
                || $params['controller']->page_name == 'module-everpsblog-post'
                || $params['controller']->page_name == 'module-everpsblog-tag'
            )
        ) {
            return $this->context->shop->theme->getLayoutRelativePathForPage(
                $params['controller']->page_name
            );
        }  else {
            return $params['default_layout'];
        }
    }

    public function hookActionFrontControllerAfterInit()
    {
        foreach (Shop::getShops() as $shop) {
            if (!$this->emptyTrash((int)$shop['id_shop'])) {
                PrestashopLogger::addLog(
                    'Trash has not been emptied for shop '.(int)$shop['id_shop']
                );
            }
        }
    }

    public function emptyTrash($id_shop)
    {
        $return = false;
        $days = (int)Configuration::get('EVERBLOG_EMPTY_TRASH');
        foreach (Language::getLanguages(false) as $language) {
            $posts = EverPsBlogPost::getPosts(
                (int)$language['id_lang'],
                (int)$id_shop,
                0,
                null,
                (string)'trash'
            );
            if (!$posts) {
                return true;
            }
            foreach ($posts as $trash_post) {
                if ((strtotime($trash_post['date_upd']) >= strtotime('-'.$days.' days'))
                ) {
                    $post = new EverPsBlogPost(
                        (int)$trash_post['id_ever_post']
                    );
                    if ($post->delete()) {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

    public function sendPendingNotification($id_shop)
    {
        $employee = new Employee(
            (int)Configuration::get('EVERBLOG_ADMIN_EMAIL')
        );
        $posts = EverPsBlogPost::getPosts(
            (int)$employee->id_lang,
            (int)$id_shop,
            0,
            0,
            'pending'
        );
        if (!count($posts)) {
            die('no pending posts');
        }
        // Todo : test pending emails
        $post_list = '';
        foreach ($posts as $pending) {
            $post = new EverPsBlogPost(
                (int)$pending['id_ever_post'],
                (int)$id_shop,
                (int)$employee->id_lang
            );
            $post_list .= '<br/><p>'.$post->title.'</p>';
        }
        $mailDir = _PS_MODULE_DIR_.'everpsblog/mails/';
        $everShopEmail = Configuration::get('PS_SHOP_EMAIL');
        $mail = Mail::send(
            (int)$this->context->language->id,
            'pending',
            $this->l('Review on pending posts'),
            array(
                '{shop_name}'=>Configuration::get('PS_SHOP_NAME'),
                '{shop_logo}'=>_PS_IMG_DIR_.Configuration::get(
                    'PS_LOGO',
                    null,
                    null,
                    (int)$this->context->shop->id
                ),
                '{posts}' => (string)$post_list
            ),
            (string)$employee->email,
            null,
            (string)$everShopEmail,
            Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            $mailDir,
            false,
            null,
            (string)$everShopEmail,
            (string)$everShopEmail,
            Configuration::get('PS_SHOP_NAME')
        );
        return $mail;
    }

    public function publishPlannedPosts($id_shop)
    {
        $context = Context::getContext();
        $posts = EverPsBlogPost::getPosts(
            (int)$context->language->id,
            (int)$id_shop,
            0,
            0,
            'planned'
        );
        if (!count($posts)) {
            die('no planned posts');
        }
        foreach ($posts as $planned) {
            $post = new EverPsBlogPost(
                (int)$planned['id_ever_post'],
                (int)$id_shop
            );
            if ($post->date_add <= date('Y-m-d H:i:s')) {
                $post->post_status = 'published';
                $post->save();
            }
        }
        return true;
    }

    public function hookActionObjectEverPsBlogPostDeleteAfter($params)
    {
        if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/posts/post_image_'.(int)$params['object']->id.'.jpg')) {
                $old_img = _PS_MODULE_DIR_.'everpsblog/views/img/posts/post_image_'.$params['object']->id.'.jpg';
                unlink($old_img);
        }
    }

    public function hookActionObjectEverPsBlogCategoryDeleteAfter($params)
    {
        if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/categories/category_image_'.(int)$params['object']->id.'.jpg')) {
                $old_img = _PS_MODULE_DIR_.'everpsblog/views/img/categories/category_image_'.$params['object']->id.'.jpg';
                return unlink($old_img);
        }
    }

    public function hookActionObjectEverPsBlogTagDeleteAfter($params)
    {
        if (file_exists(_PS_MODULE_DIR_.'everpsblog/views/img/tags/tag_image_'.(int)$params['object']->id.'.jpg')) {
                $old_img = _PS_MODULE_DIR_.'everpsblog/views/img/tags/tag_image_'.$params['object']->id.'.jpg';
                return unlink($old_img);
        }
    }
}
