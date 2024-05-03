<?php
/**
 * VoucherList Listing
 * @author  <your name here>
 */
class VoucherList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Voucher');
        $this->form->setFormTitle('Voucher');
        

        // create the form fields
        $voucher_id = new TDBUniqueSearch('voucher_id', 'communication', 'Voucher', 'voucher_id', 'cpf_cliente');
        $cpf_cliente = new TEntry('cpf_cliente');
        $campanha_id = new TDBUniqueSearch('campanha_id', 'communication', 'Campanha', 'campanha_id', 'nome');
        $codigo = new TEntry('codigo');
        $data_criacao = new TEntry('data_criacao');
        $data_uso = new TEntry('data_uso');
        $usuario_uso_id = new TEntry('usuario_uso_id');
        $ip_uso = new TEntry('ip_uso');
        $ip_criacao = new TEntry('ip_criacao');


        // add the fields
        $this->form->addFields( [ new TLabel('Voucher Id') ], [ $voucher_id ] );
        $this->form->addFields( [ new TLabel('Cpf Cliente') ], [ $cpf_cliente ] );
        $this->form->addFields( [ new TLabel('Campanha Id') ], [ $campanha_id ] );
        $this->form->addFields( [ new TLabel('Codigo') ], [ $codigo ] );
        $this->form->addFields( [ new TLabel('Data Criacao') ], [ $data_criacao ] );
        $this->form->addFields( [ new TLabel('Data Uso') ], [ $data_uso ] );
        $this->form->addFields( [ new TLabel('Usuario Uso Id') ], [ $usuario_uso_id ] );
        $this->form->addFields( [ new TLabel('Ip Uso') ], [ $ip_uso ] );
        $this->form->addFields( [ new TLabel('Ip Criacao') ], [ $ip_criacao ] );


        // set sizes
        $voucher_id->setSize('100%');
        $cpf_cliente->setSize('100%');
        $campanha_id->setSize('100%');
        $codigo->setSize('100%');
        $data_criacao->setSize('100%');
        $data_uso->setSize('100%');
        $usuario_uso_id->setSize('100%');
        $ip_uso->setSize('100%');
        $ip_criacao->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__ . '_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['VoucherForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_voucher_id = new TDataGridColumn('voucher_id', 'Voucher Id', 'right');
        $column_cpf_cliente = new TDataGridColumn('cpf_cliente', 'Cpf Cliente', 'left');
        $column_campanha_id = new TDataGridColumn('campanha_id', 'Campanha Id', 'right');
        $column_codigo = new TDataGridColumn('codigo', 'Codigo', 'left');
        $column_data_criacao = new TDataGridColumn('data_criacao', 'Data Criacao', 'left');
        $column_data_uso = new TDataGridColumn('data_uso', 'Data Uso', 'left');
        $column_usuario_uso_id = new TDataGridColumn('usuario_uso_id', 'Usuario Uso Id', 'right');
        $column_ip_uso = new TDataGridColumn('ip_uso', 'Ip Uso', 'left');
        $column_ip_criacao = new TDataGridColumn('ip_criacao', 'Ip Criacao', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_voucher_id);
        $this->datagrid->addColumn($column_cpf_cliente);
        $this->datagrid->addColumn($column_campanha_id);
        $this->datagrid->addColumn($column_codigo);
        $this->datagrid->addColumn($column_data_criacao);
        $this->datagrid->addColumn($column_data_uso);
        $this->datagrid->addColumn($column_usuario_uso_id);
        $this->datagrid->addColumn($column_ip_uso);
        $this->datagrid->addColumn($column_ip_criacao);


        $action1 = new TDataGridAction(['VoucherForm', 'onEdit'], ['voucher_id'=>'{voucher_id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'], ['voucher_id'=>'{voucher_id}']);
        
        $this->datagrid->addAction($action1, _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action2 ,_t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        
        parent::add($container);
    }
    
    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('communication'); // open a transaction with database
            $object = new Voucher($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_voucher_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_cpf_cliente',   NULL);
        TSession::setValue(__CLASS__.'_filter_campanha_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_codigo',   NULL);
        TSession::setValue(__CLASS__.'_filter_data_criacao',   NULL);
        TSession::setValue(__CLASS__.'_filter_data_uso',   NULL);
        TSession::setValue(__CLASS__.'_filter_usuario_uso_id',   NULL);
        TSession::setValue(__CLASS__.'_filter_ip_uso',   NULL);
        TSession::setValue(__CLASS__.'_filter_ip_criacao',   NULL);

        if (isset($data->voucher_id) AND ($data->voucher_id)) {
            $filter = new TFilter('voucher_id', '=', $data->voucher_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_voucher_id',   $filter); // stores the filter in the session
        }


        if (isset($data->cpf_cliente) AND ($data->cpf_cliente)) {
            $filter = new TFilter('cpf_cliente', 'like', "%{$data->cpf_cliente}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_cpf_cliente',   $filter); // stores the filter in the session
        }


        if (isset($data->campanha_id) AND ($data->campanha_id)) {
            $filter = new TFilter('campanha_id', '=', $data->campanha_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_campanha_id',   $filter); // stores the filter in the session
        }


        if (isset($data->codigo) AND ($data->codigo)) {
            $filter = new TFilter('codigo', 'like', "%{$data->codigo}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_codigo',   $filter); // stores the filter in the session
        }


        if (isset($data->data_criacao) AND ($data->data_criacao)) {
            $filter = new TFilter('data_criacao', 'like', "%{$data->data_criacao}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_data_criacao',   $filter); // stores the filter in the session
        }


        if (isset($data->data_uso) AND ($data->data_uso)) {
            $filter = new TFilter('data_uso', 'like', "%{$data->data_uso}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_data_uso',   $filter); // stores the filter in the session
        }


        if (isset($data->usuario_uso_id) AND ($data->usuario_uso_id)) {
            $filter = new TFilter('usuario_uso_id', 'like', "%{$data->usuario_uso_id}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_usuario_uso_id',   $filter); // stores the filter in the session
        }


        if (isset($data->ip_uso) AND ($data->ip_uso)) {
            $filter = new TFilter('ip_uso', 'like', "%{$data->ip_uso}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_ip_uso',   $filter); // stores the filter in the session
        }


        if (isset($data->ip_criacao) AND ($data->ip_criacao)) {
            $filter = new TFilter('ip_criacao', 'like', "%{$data->ip_criacao}%"); // create the filter
            TSession::setValue(__CLASS__.'_filter_ip_criacao',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'communication'
            TTransaction::open('communication');
            
            // creates a repository for Voucher
            $repository = new TRepository('Voucher');
            $limit = 10;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'voucher_id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue(__CLASS__.'_filter_voucher_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_voucher_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_cpf_cliente')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_cpf_cliente')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_campanha_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_campanha_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_codigo')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_codigo')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_data_criacao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data_criacao')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_data_uso')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_data_uso')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_usuario_uso_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_usuario_uso_id')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_ip_uso')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_ip_uso')); // add the session filter
            }


            if (TSession::getValue(__CLASS__.'_filter_ip_criacao')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_ip_criacao')); // add the session filter
            }

            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('communication'); // open a transaction with database
            $object = new Voucher($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
