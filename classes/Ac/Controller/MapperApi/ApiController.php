<?php

/** 
 * RESTish api to access mappers' records.
 * 
 * Requests may be done either using JSON or using form-data.
 * 
 * Default format of SEF urls are:
 * /list/
 * /item/{key} - GET returns item; POST (key not required) creates item; PATCH updates item; DELETE deletes item.
 * /load/{key} - always returns item (like in GET /item/{key}/)
 * /delete[/{key(s)}] - POST only; deletes items by keys; if keys provided in URI, they MUST NOT be provided in data
 * /create - POST only; if top-level is numeric array, will create many items
 * /update[/{key}] - POST only; if top-level is numeric array, will update many items; GET works as 'validate' (no actual change)
 * /validate[/{key}] - like update, but performs checks for errors only, does not actually save
 * /deleteMany/ - POST only; will delete all matching records; requires either filters or ?all=1
 * /updateMany/ - POST only; will update all matching records; requires either filters or ?all=1
 * /offset/{key} - filters & sorting as in list; key is required; will return offset of an item with specified key
 * 
 * Supplying record keys:
 * 
 * - _key may be used everywhere where key is expected
 * - key field may be also supplied as part of data (i.e. when PK field is "id", "id" field may be used in post'ed data)
 * - when one needs to update record key, both "_key" and "(key field)" values must be present
 * 
 * Query string arguments:
 * 
 * - mapperId (usually encoded in SEF URI)
 * 
 * - key (in GET arg; usually encoded in SEF URI)
 * 
 * - field list for item & list:
 *   -  props={prop,prop...prop}; default to all except related
 *   -  propsExclude={prop,prop...prop} - exclude from default list of fields
 *   -  propsInclude={prop,prop...prop} - props to add
 * 
 * - filters: 
 * 
 *   -  f-{field}, o, o-{field}, s - omni filters (if supported)
 *   -  q-{criterion} - search query values
 * 
 * - sorting:
 * 
 *   -  sort=field[-ASC|-DESC]
 * 
 * - pagination:
 * 
 *    - offset
 *    - limit
 *    - noCount=1 - to suppress counting of items for list method
 * 
 */

class Ac_Controller_MapperApi_ApiController extends Ac_Controller {

    const API_SETTINGS_DEFAULT = '_default_';

    var $addAnyOrder = true;
    
    var $addOmniFilter = true;
    
    var $orderPart = 'anyOrder';
    
    var $omniFilterPart = 'omniFilter';
    
    // if set, this value both overrides allowedMappers AND won't require mapper Id as first parameter of URL
    
    var $onlyMapperId = null;
    
    // set to true to allow all mappers
    
    var $allowedMappers = [];

    var $deniedMappers = [];
    
    // set to false to allow anonymous access
    
    var $requireUser = true;
    
    var $mapperCommandPrefix = 'api';
    
    var $keyParam = 'key';
    
    var $altKeyParam = '_key';
    
    var $keyValueSeparator = ','; // change or set to NULL to disable keys splitting
    
    var $mapperIdMap = []; // param value to app mapper Id
    
    var $useUrlMapper = true;
    
    protected $apiSettings = [];

    /**
     * @var Ac_Controller_MapperApi_ApiSettings
     */
    protected $effectiveApiSettings = null;
    
    protected $mapperId = false;
    
    protected $keyValue = false; // key param value retrieved from URI
    
    function doBeforeExecute() {
        //$methodName = func_get_arg(0);
        //Ac_Debug::ddd($methodName, $this->_context->getData(), $this->getMethodName(), $this->getMethodParamValue(), $this->_methodParamName);
    }
    
    function setApiSettings(array $apiSettings = []) {
        $this->apiSettings = Ac_Prototyped::factoryCollection(
            $apiSettings, 
            'Ac_Controller_MapperApi_ApiSettings',
            ['application' => $this->application],
            'mapperId',
            true
        );
    }
    
    /**
     * @return Ac_Controller_MapperApi_ApiSettings[]
     */
    function getApiSettings() {
        return $this->apiSettings;
    }
    
    /**
     * @return Ac_Controller_MapperApi_ApiSettings
     */
    function getApiSettingsForMapper($mapperId) {
        if (isset($this->apiSettings[$mapperId])) return $this->apiSettings[$mapperId];
        if (isset($this->apiSettings[self::API_SETTINGS_DEFAULT])) {
            return $this->apiSettings[self::API_SETTINGS_DEFAULT];
        }
        return $this->createFallbackApiSettings();
    }
    
    /**
     * @return Ac_Controller_MapperApi_ApiSettings
     */
    protected function createFallbackApiSettings() {
        return new Ac_Controller_MapperApi_ApiSettings(['application' => $this->application]);
    }
    
    /**
     * @return Ac_Controller_MapperApi_ApiSettings
     */
    protected function getEffectiveApiSettings() {
        $id = $this->getMapperId();
        if ($this->effectiveApiSettings && $this->effectiveApiSettings->getMapperId() === $id) {
            return $this->effectiveApiSettings;
        }
        $res = $this->getApiSettingsForMapper($id);
        if ($res->getMapperId() !== $id) {
            if (!$res->getMapperId()) $res = clone $res;
            $res->setMapperId($id);
        }
        $this->effectiveApiSettings = $res;
        return $res;
    }
    
    protected function getPropList($paramName) {
        $list = $this->param($paramName);
        if ($list === null) return [];
        if (!is_array($list)) $list = explode(',', $list);
        return array_map(['Ac_Util', 'kebabPathToBrackets'], $list);
    }
    
    protected function produceEffectivePropList() {
        $apiSettings = $this->getEffectiveApiSettings();
        $res = $apiSettings->getProps(
            $this->getPropList('props'), 
            $this->getPropList('propsInclude'), 
            $this->getPropList('propsExclude')
        );
        return $res;
    }
    
    protected function toJson($object, array & $props = null) {
        if (is_null($props)) $props = $this->produceEffectivePropList();
        $res = Ac_Accessor::getObjectProperty($object, $props);
        return $res;
    }
    
    function getMapperId($dontValidate = false) {
        
        if ($this->mapperId !== false) return $this->mapperId;
        
        $argMapperId = null;
        
        if ($this->onlyMapperId || $dontValidate) {
            $argMapperId = $this->param('mapperId', $this->onlyMapperId);
        } else {
            $argMapperId = $this->require('mapperId');
        }
        
        if (isset($this->mapperIdMap[$argMapperId])) {
            $mapperId = $this->mapperIdMap[$argMapperId];
        } else {
            $mapperId = $argMapperId;
        }
        
        if ($dontValidate) return $mapperId;
        
        if (!is_null($this->onlyMapperId)) $allowedMappers = [$this->onlyMapperId];
        else $allowedMappers = $this->allowedMappers;
        
        if ($allowedMappers === true) {
            // allow all mappers
        } else if (!in_array($argMapperId, $allowedMappers)) {
            throw new Ac_E_ControllerException("Access to mapper '{$argMapperId}' isn't allowed", 403);
        }
        
        if ($this->deniedMappers && in_array($argMapperId, $this->deniedMappers)) {
            throw new Ac_E_ControllerException("Access to mapper '{$argMapperId}' is denied", 403);
        }
        
        if (!$this->application->getMapper($argMapperId, true)) {
            throw new Ac_E_ControllerException("No such mapper: '{$argMapperId}'", 403);
        }
        
        $this->mapperId = $argMapperId;
        
        return $this->mapperId;
    }
    
    function getKeyFromRequest($require = false) {
        $res = $this->getKeysArrayFromRequest($require);
        if (count($res)) return $res[0];
        return null;
    }
    
    function getKeysArrayFromRequest($require = false) {
        $keyValue = $this->param($this->keyParam);
        if (is_null($keyValue)) $keyValue = $this->param($this->altKeyParam);
        if (!is_array($keyValue)) {
            if ($this->keyValueSeparator) $keyValue = explode($this->keyValueSeparator, $keyValue);
            else $keyValue = Ac_Util::toArray($keyValue);
        }
        $keyValue = array_values(Ac_Util::flattenArray($keyValue));
        if ($require && !$keyValue) {
            throw new Ac_E_ControllerException("One of required parameters '{$this->keyParam}' or '{$this->altKeyParam}' is missing", 400);
        }
        return $keyValue;
    }

    
    /**
     * @return Ac_Model_Mapper
     */    
    function getMapper() {
        return $this->application->getMapper($this->getMapperId());
    }
    
    function getUrlMapperPrototype() {
       $res = [
           'class' => 'Ac_UrlMapper_StaticSignatures',
           'methodParamName' => $this->_methodParamName,
           'controllerClass' => get_class($this),
           'patterns' => [
               [
                   'definition' => '/listMappers', 
                   'const' => [$this->_methodParamName => 'listMappers']
               ]
            ],
            'argRegexes' => [
                'key' => '[^/]+',
            ],
           'ignoreMethods' => [
               'listMappers',
               'execute',
            ],
       ];
       if (!$this->onlyMapperId) {
           $res['prefix'] = '/{mapperId}';
       }
       return $res;
    }
    
    function execute() {
        return [];
    }
    
    function executeListMappers() {
        return $this->application->listMappers();
    }
    
    function executeItem($key) {
        $key = $this->getKeyFromRequest(true);
        return $this->executeLoad($key);
    }
    
    function executeItems($key) {
        $keys = $this->getKeysArrayFromRequest(true);
        $mapper = $this->getMapper();
        $items = $mapper->loadRecordsArray($keys);
        $res = [];
        foreach ($items as $item) $res[] = $this->toJson($item);
        return $res;
    }
    
    function executeLoad($key) {
        $mapper = $this->getMapper();
        $item = $mapper->loadRecord($key);
        if ($item) return $this->toJson($item);
        return [];
    }
    
    protected function getQuery() {
        $res = [];
        $omni = [];
        
        $data = $this->getContext()->getData();
        if (isset($data['filter'])) {
            $data = array_merge($data, $data['filter']);
            unset($data['filter']);
        }
        if (isset($data['o'])) {
            $omni = [' json ' => $data['o']];
        }
        foreach ($data as $k => $v) {
            if (substr($k, 0, 2) == 'o-') {
                if (!isset($data['o'])) {
                    $fieldName = Ac_Util::kebabPathToBrackets(substr($k, 2));
                    $omni[$fieldName] = $v;
                }
                continue;
            }
            if (substr($k, 0, 2) != 'q_') continue;
            $res[substr($k, 2)] = $v;
        }
        
        if ($omni && $this->omniFilterPart) {
            $res[$this->omniFilterPart] = $omni;
        }
        
        if ($this->orderPart) {
            $sortValue = $this->param('sort');
            if (!is_null($sortValue)) {
                $res[$this->orderPart] = $sortValue;
            }
        }
        
        return $res;
    }
    
    /**
     * @return Ac_Model_Collection_Mapper
     */
    protected function createCollection() {
        
        $mapper = $this->getMapper();
        
        $coll = $mapper->createCollection();
        
        if ($this->addAnyOrder && $coll instanceof Ac_Model_Collection_SqlMapper) {
            $proto = $coll->getSqlSelectPrototype();
            Ac_Util::ms($proto, [
                'parts' => [
                    $this->orderPart => [
                        'class' => 'Ac_Sql_Order_Any',
                        'defaultAlias' => 't',
                    ],
                ],
            ]);
            $coll->setSqlSelectPrototype($proto);
        }
        
        if ($this->addOmniFilter && $coll instanceof Ac_Model_Collection_SqlMapper) {
            $proto = $coll->getSqlSelectPrototype();
            Ac_Util::ms($proto, [
                'parts' => [
                    $this->omniFilterPart => [
                        'class' => 'Ac_Sql_Filter_Omni',
                        'defaultAlias' => 't',
                        'fieldsNotation' => true,
                    ],
                ],
            ]);
            $coll->setSqlSelectPrototype($proto);
        }
        
        $apiSettings = $this->getEffectiveApiSettings();
        $limit = $this->param('limit', null);
        if (is_null($limit)) {
            $limit = $apiSettings->defaultLimit;
        } elseif (!strlen($limit)) {
            $limit = '';
        } else {
            $limit = (int) $limit;
            if ($limit < 0) $limit = 0;
        }
        if ($apiSettings->maxLimit) {
            if (!$limit || $limit > $apiSettings->maxLimit) {
                $limit = $apiSettings->maxLimit;
            }
        }
        if ($limit === '') $limit = null;
        
        $offset = (int) $this->param('offset', 0);

        $query = $this->getQuery();
        $coll->setQuery($query);
        $coll->setLimit($limit === null? false : $limit);
        $coll->setOffset($offset);
        
        return $coll;
        
    }
    
    function executeList() {
        $coll = $this->createCollection();
        
        $records = [];
        
        while ($group = $coll->fetchGroup()) {
            foreach ($group as $rec) {
                $records[] = $this->toJson($rec, $props);
                $rec->cleanupMembers();
            }
        }
        
        $res = [
            'totalRecords' => $coll->getCount(),
            'lastFoundRows' => $coll->getCount(),
            'query' => $this->getQuery(),
            'offset' => $coll->getOffset()? $coll->getOffset() : 0,
            'limit' => $coll->getLimit()? $coll->getLimit() : 0,
            'records' => $records,
        ];
        
        return $res;        
    }
    
    function executeNames() {
        
        $coll = $this->createCollection();
        $titleField = $this->getMapper()->getTitleFieldName();
        
        while ($group = $coll->fetchGroup()) {
            foreach ($group as $rec) {
                $res[$rec->getIdentifier()] = $rec->getField($titleField);
                $rec->cleanupMembers();
            }
        }
        
        return new Ac_Js_Hash($res);        
    }
    
    /**
     * returns metadata for the records in specified mapper
     */
    function executeMeta() {
    }
    
    function executeCreate() {
        $mapper = $this->getMapper();
        $data = $_POST;
        $pk = $mapper->pk;
        if (!isset($data[$pk]) || !strlen(''.$data[$pk])) {
            unset($data[$pk]);
        }
        $item = $mapper->createRecord($data);
        if (!$item->check() || !$item->save()) {
            return [
                'success' => 0,
                'errors' => $item->getErrors()
            ];
        }
        //$item->load();
        return [
            'success' => 1,
            'key' => $item->getIdentifier(),
            'record' => $this->toJson($item)
        ];
    }
    
    function executeUpdate($key = null) {
        $key = $this->getKeyFromRequest();
        $mapper = $this->getMapper();
        $item = $mapper->loadRecord($key);
        if (!$item) {
            return [
                'success' => 0,
                'errors' => [
                    '_key' => 'Record not found',
                ],
            ];
        }
        $item->bind($_POST);
        if (!$item->check() || !$item->save()) {
            return [
                'success' => 0,
                'errors' => $item->getErrors()
            ];
        }
        return [
            'success' => 1,
            'record' => $this->toJson($item)
        ];
    }
    
    function executeDelete($key = null) {
        $key = $this->getKeyFromRequest();
        $mapper = $this->getMapper();
        $item = $mapper->loadRecord($key);
        if (!$item) {
            return [
                'success' => 0,
                'errors' => [
                    '_key' => 'Record not found',
                ],
            ];
        }
        if (!$item->delete()) {
            return [
                'success' => 0,
                'errors' => $item->getErrors(),
            ];
        }
        return [
            'success' => 1,
        ];
    }
    
    function executeDeleteMany() {
    }
    
    function executeUpdateMany() {
    }
    
    function executeOffset($key) {
    }
    
}