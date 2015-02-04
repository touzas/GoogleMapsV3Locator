<?php
class geoLocalization{

    private $dbCon;
    
    var $markers;

    /**
     * Constructor de la clase.
     * @param string $dbName
     * @param string $tblName
     */
    public function __construct($server, $user, $passwd, $dbName, $tblName) {
        //$this->table = $tblName;
        //$this->dbCon = mysql_connect($server, $user, $passwd) or die('Cannot connect');
        //mysql_select_db($dbName);        
    }

    public function db_getrows($query){
        //$res = mysql_query($query, $this->dbCon);
        //$result = array();
        //while($item = mysql_fetch_object($res, $this->dbCon)){
        //    $result[] = $item;
        //}
        //return $result;
    }
    public function db_getrow($query){
        //$result = null;
        //$res = mysql_query($query, $this->dbCon);
        //while($item = mysql_fetch_object($res, $this->dbCon)){
        //    $result = $item;
        //}
        //return $result;
    }
    public function db_execute($query){
        //$res = mysql_query($query, $this->dbCon);
    }

    /**
     * Guarda las posiciones que se hayan cambiado.
     * @param array $data
     * @return string
     */
    public function savePositions($data){
        $changes = json_decode($data);

        if (is_array($changes) && count($changes) > 0){
            foreach($changes as $value){
                /**
                 * 
                 * Insert a code to update google maps positions
                 * 
                 */
                 $longitud = $value->lng;
                 $latitud = $value->lat;
            }
        }
        return array(
            'status' => 'OK',
            'message' => 'Datos guardados correctamente'
        );
    }

    /**
     * Guarda la cita concertada
     * @param float $itemId
     * @param Datetime $fecha
     * @param int $operario
     * @param string $observaciones
     * @return string
     */
    public function saveAppointment($itemId, $fecha, $operario, $estado = 1, $observaciones){
        $date = DateTime::createFromFormat('d/m/Y H:i', $fecha);
        $fechaVisita = $date->format('Y-m-d H:i:s');
        
        /**
         * 
         * Insert a code to create new Appointment
         * 
         */

        return array(
            'status' => 'OK',
            'message' => 'Datos guardados correctamente'
        );
    }    
    /**
     * Gestiona las peticiones de información para cada dirección
     * @param string $itemId
     * @return string
     */
    public function getInfo($itemId){                
        $data = new stdClass();
        $telefonos = "<a href='callto:".$data->telefono."'>".$data->telefono."</a>";
        $data->telefonos = $telefonos;

        $observaciones = ($data->observaciones)?$data->observaciones:' - ';
        $data->observaciones = $observaciones;

        $data->hst .= '
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-4"><b>'.$historico->fecha.'</b></div>
                        <div class="col-xs-4 text-center"><b>'.$historico->estado.'</b></div>
                        <div class="col-xs-4 text-right"><b>'.$historico->id.'</b></div>
                    </div>
                </div>
                <div class="panel-body">
                    '.$obs.'
                </div>
            </div>
        ';
        $data->hst = $ordenes;

        $operarios = $this->getOperarios(true);
        $data->operarios = $operarios;

        $data->urlHst = "http://URL_TO_ACCESS_TO_HISTORICAL?id=".$data->idordengn;
        $data->urlCliente = "http://URL_TO_ACCESS_CLIENT?id=".$data->idcliente;

        $result = $this->renderInfo($data, $itemId);

        return $result;
    }

    /**
     * Devuelve los tecnicos a los que puede asignarse una orden
     * @param bool $restrictions
     * @return string Listado de opciones de los operarios
     */
    private function getOperarios($restrictions = false){
        $operarios = '<option value=""> Seleccione </option>';
        $operarios .= '<option value="1">Operario 1</option>';
        $operarios .= '<option value="2">Operario 2</option>';
        $operarios .= '<option value="3">Operario 3</option>';
        
        return $operarios;
    }
    

    /**
     * Devuelve el formulario de información para cada dirección
     * @param type $data
     * @param type $itemId
     * @return type
     */
    private function renderInfo($data, $itemId){
        ob_start();
        ?>
        <div id="infowindow_content" class="container-fluid" style="padding: 10px;">
            <div class="panel panel-primary">
                <div class="panel-heading">Información de la orden</div>
                <div class="panel-body">
                    <dl>
                        <dt>Item id: </dt>
                        <dd>
                            <?php echo $data->id;?>
                            <?php 
                                if (isset($data->urlOrden) && $data->urlHst != ''){
                                        echo "<a href='".$data->urlHst."' target='_blank'>Ir al histórico</a>";
                                }
                            ?>
                        </dd>
                    </dl>
                    <dl>
                        <dt>Cliente: </dt>
                        <dd>
                            <?php echo $data->cliente;?>
                            <?php 
                                if (isset($data->urlCliente) && $data->urlCliente != ''){
                                    echo "<a href='".$data->urlCliente."' target='_blank'>Ir a la ficha</a>";
                                }
                            ?>
                        </dd>
                    </dl>
                    <dl>
                        <dt>Teléfonos: </dt>
                        <dd><?php echo $data->telefonos;?></dd>
                    </dl>
                    <dl>
                        <dt>Dirección: </dt>
                        <dd><?php echo $data->direccion;?></dd>
                    </dl>
                <?php
                if ($data->estado != ''){
                    ?>
                    <dl>
                        <dt>Estado: </dt>
                        <dd><?php echo $data->estado; ?></dd>
                    </dl>
                    <?php
                }       
                if ($data->hst != ''){
                    ?>
                    <dl>
                        <dt>Histórico: </dt>
                        <dd><a href="javascript:showDetails('hstDetails');" id="showDetails">Mostrar detalles</a></dd>
                    </dl>
                    <dl>
                        <dd class="col-lg-12">
                            <div id="hstDetails" style="display:none">
                                <?php echo $data->hst;?>
                            </div>
                        </dd>
                    </dl>
                    <?php
                }
                ?>
                    <dl>
                        <dt>Observaciones: </dt>
                        <dd><?php echo $data->observaciones;?></dd>
                    </dl>
                    <dl>
                        <dd class="col-lg-12">
                            <div class="navbar">
                                <button type="button" class="btn btn-primary btn-xs" aria-label="Left Align" onclick="javascript:getNearby('<?php echo $itemId;?>');">
                                    <span class="glyphicon glyphicon-zoom-in" aria-hidden="true"></span>
                                    Próximas
                                    <span class="badge" id="badge_nearby">0</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-xs" aria-label="Left Align" onclick="javascript:getSameDirection('<?php echo $itemId;?>');">
                                    <span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>
                                    Misma dirección
                                    <span class="badge" id="badge_sameDirection">0</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-xs" aria-label="Left Align" onclick="javascript:addAppointment();">
                                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                    Concertar cita
                                </button>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
            <div id="moreInfo"></div>
            <div class="panel panel-info" id="addAppointment" style="display:none">
                <div class="panel-heading">Concercar cita</div>
                <div class="panel-body">
                    <form id="frmAppointment">
                        <input type="hidden" name="itemId" value="<?php echo $itemId;?>"/>
                        <input type="hidden" name="opcion" value="saveAppointment"/>
                        <div class="row">
                            <div class="col-xs-4">
                                <label for="fecha"><?php echo $fecha; ?></label>
                            </div>
                            <div class="col-xs-8">
                                <input type="text" class="form-control input-sm" id="fecha" name="fecha" placeholder="dd/mm/aaaa hh:mm">
                                <label>dd/mm/aaaa hh:mm</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4">
                                <label for="operario">Operario</label>
                            </div>
                            <div class="col-xs-8">
                                <select class="form-control input-sm" id="operario" name="operario">
                                    <?php echo $data->operarios;?>
                                </select>
                            </div>
                        </div>        
                        <div class="row">
                            <div class="col-xs-4">
                                <label for="observaciones">Observaciones</label>
                            </div>
                            <div class="col-xs-8">
                                <textarea class="form-control input-sm" id="observaciones" name="observaciones" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="row text-right">
                            <div class="btn-group" role="group" aria-label="actions">
                                <button type="button" class="btn btn-primary btn-xs" aria-label="Left Align" onclick="javascript:saveAppointment();">
                                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                                    Guardar
                                </button>
                                <button type="button" class="btn btn-danger btn-xs" aria-label="Left Align" onclick="showDetails('addAppointment');">
                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }    

    /**
     * Devuelve un json con los items a pintar en el mapa.
     * @return string
     */
     public function getAddress(){
        $markers = array();

        $item = array();
        $item['itemId'] = 1;
        $item['lon'] = '-8.3551025390625';
        $item['lat'] = '43.29419986119423';        
        $item['address'] = utf8_encode("Cambre");
        $item['locality'] = utf8_encode("Coruña");
        array_push($markers, $item);
        
        $item = array();
        $item['itemId'] = 2;
        $item['lon'] = '-7.55859375';
        $item['lat'] = '43.01669737169671';        
        $item['address'] = utf8_encode("Lugo");
        $item['locality'] = utf8_encode("Lugo");
        array_push($markers, $item);
        
        $item = array();
        $item['itemId'] = 3;
        $item['lon'] = '-8.52813720703125';
        $item['lat'] = '42.884014670442525';        
        $item['address'] = utf8_encode("Santiago de Compostela");
        $item['locality'] = utf8_encode("Coruña");
        array_push($markers, $item);
        
        $item = array();
        $item['itemId'] = 4;
        $item['address'] = utf8_encode("Rúa Manuel Murguía, S/N, 15011 A Coruña");
        $item['locality'] = utf8_encode("Coruña");
        array_push($markers, $item);

        return $markers;
    }   

}

$json = true;
$mng = new geoLocalization("dbserver", "dbuser", "dbpass", "dbName", "tableName");
$result = '';

if (isset($_POST["opcion"])){
    if ($_POST["opcion"] == 'savePositions'){

        $data = filter_input(INPUT_POST, 'data');
        $result = $mng->savePositions($data);

    }else if ($_POST["opcion"] == 'saveAppointment'){

        $itemId = filter_input(INPUT_POST, 'itemId');
        $fecha = filter_input(INPUT_POST, 'fecha');
        $operario = filter_input(INPUT_POST, 'operario');
        $estado_averia = filter_input(INPUT_POST, 'estado_averia');
        $estado = ($estado_averia == '')?1:$estado_averia;
        $observaciones = filter_input(INPUT_POST, 'observaciones');

        $result = $mng->saveAppointment($itemId, $fecha, $operario, $estado, $observaciones);
    }
}else if (isset($_GET["opcion"])){

    if ($_GET["opcion"] == 'info'){

        $itemId = filter_input(INPUT_GET, 'itemId');
        $result = $mng->getInfo($itemId);
        $json = false;

    }else if ($_GET["opcion"] == 'getData'){

        $data = $mng->getAddress();
        $result = array(
            'title' => 'Direcciones postales',
            'data' => $data
        );

    }

}
echo ($json)?json_encode($result):$result;
