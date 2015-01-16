<?php
	session_start();
?>
<?php
class Nnacontr extends CI_Controller {
		
	public function __construct(){ 
       parent::__construct(); 
	   $this->load->model('Nnamodel','',TRUE);
	   #$this->load->library('pagination');
	   $this->load->helper('url','form');
	   $this->load->library('form_validation');
	   #$this->load->helper('file');
	}
	
	function validar_fecha($str){
		if (strlen($str)>0)
		{
			$patron="/^(\d){2}\/(\d){2}\/(\d){4}$/i";
			if (preg_match($patron,$str))
			{
				return TRUE;
			}
			else
			{
				$this->form_validation->set_message('validar_fecha','formato de fecha no v&aacute;lido');
				return FALSE;
			}
		}
		else
		{
			return TRUE;
		}
	}
	/****VALIDAR EL SEXO DE UN NNA*****/
	function validar_sexo($tSex)
	{
		if($tSex=='M' || $tSex=='F')
		{
			return TRUE;
		}
		else
		{	$this->form_validation->set_message('validar_sexo','Sexo incorrecto');
			return FALSE;
		}
	}
	
	/***********/
	private function vld_session(){
		if(!isset($_SESSION['__USER_ID']))
			redirect(base_url());
	}
  /**CALCULAR LA EDAD DE UN NNA EN BASE A LA FECHA DE NACIMIENTO**/
	private function pcd_edad_nna($FechaNac,&$anos,&$meses){


		$fecha_nac_aux = explode ("/",$FechaNac);
		$fecha_actual = date ("Y-m-d"); 
		
		// separamos en partes las fechas
		//$array_nacimiento= date_format($FechaNac, 'Y-m-d'); 
		//$array_nacimiento = explode ( "-", $fecha_de_nacimiento ); 
		$array_actual = explode ( "-", $fecha_actual ); 
		$anos =  $array_actual[0] - $fecha_nac_aux[2];//$array_nacimiento[0]; // calculamos años 
		$meses = $array_actual[1] - $fecha_nac_aux[1];//$array_nacimiento[1]; // calculamos meses 
		$dias =  $array_actual[2] - $fecha_nac_aux[0];//$array_nacimiento[2]; // calculamos días 
		
		//ajuste de posible negativo en $días 
		if ($dias < 0) 
		{ 
			--$meses; 
		
			//ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual 
			switch ($array_actual[1]) { 
				   case 1:     $dias_mes_anterior=31; break; 
				   case 2:     $dias_mes_anterior=31; break; 
				   case 3:  
						if (es_anio_bisiesto($array_actual[0])) 
						{ 
							$dias_mes_anterior=29; break; 
						} else { 
							$dias_mes_anterior=28; break; 
						} 
				   case 4:     $dias_mes_anterior=31; break; 
				   case 5:     $dias_mes_anterior=30; break; 
				   case 6:     $dias_mes_anterior=31; break; 
				   case 7:     $dias_mes_anterior=30; break; 
				   case 8:     $dias_mes_anterior=31; break; 
				   case 9:     $dias_mes_anterior=31; break; 
				   case 10:     $dias_mes_anterior=30; break; 
				   case 11:     $dias_mes_anterior=31; break; 
				   case 12:     $dias_mes_anterior=30; break; 
			} 
		
			$dias=$dias + $dias_mes_anterior; 
		} 
		
		//ajuste de posible negativo en $meses 
		if ($meses < 0) 
		{ 
			--$anos; 
			$meses=$meses + 12; 
		} 
		
		//echo "<br>Tu edad es: $anos años con $meses meses y $dias días"; 
	}
	/***Función que determina si un año es bisiesto**/
	private function es_anio_bisiesto($anio_actual){ 
		$bisiesto=false; 
		//probamos si el mes de febrero del año actual tiene 29 días 
		  if (checkdate(2,29,$anio_actual)) 
		  { 
			$bisiesto=true; 
		} 
		return $bisiesto; 
	} 


  /******Registra un nuevo NNA******/   
    public function registro_nna(){
		$this->vld_session();
        //$this->form_validation->set_rules('t_AM','Ap mat','required');//|min_length[5]|max_length[12]	  
        //$this->form_validation->set_rules('t_AP','Ap pat','required');		
        $this->form_validation->set_rules('t_N','Nombre','required');//|min_length[5]|max_length[12]
        $this->form_validation->set_rules('t_FN','Fec. nac.','callback_validar_fecha');
		$this->form_validation->set_rules('t_Edad_A','Edad','required');
	    $this->form_validation->set_rules('rd_sexo','Sexo','required|callback_validar_sexo');
		//
		$this->form_validation->set_message('required', '"%s" es requerido.');
		$this->form_validation->set_message('integer', '"%s"  debe ser numero entero.');
		$this->form_validation->set_message('min_length', 'El campo %s debe ser de al menos %s carácteres');
		//$this->form_validation->set_message('valid_email', 'Debe escribir una dirección de email correcta');
		//$this->form_validation->set_message('min_length[5]', 'Longitud nnn???');
		//$this->form_validation->set_message('max_length', 'Longitud nnn???');
		//$this->form_validation->set_message('matches', 'Los campos %s y %s no coinciden');
        // 
	   if($this->form_validation->run()==FALSE)
	     { 
		  $this->load->view('observatorio/modulos/encabezadoContenido.php');
		  $data['error_data'] = "No se proceso nada";
          $this->load->view('observatorio/modulos/mod_nna/registrar_nuevo.php',$data);
	     }
	   else
	     {
		  $_app_pat=strtoupper(trim($this->input->post('t_AP')));
          $_app_mat=strtoupper(trim($this->input->post('t_AM')));
		  $_nombres=strtoupper(trim($this->input->post('t_N')));
		  $_sexo=strtoupper(trim($this->input->post('rd_sexo')));
		  $_fecha_nac=$this->input->post('t_FN');
		  $_edad_anio=$this->input->post('t_Edad_A');
		  $_edad_mes=0;		  
		  if(isset($_POST['t_Edad_M'])){
			if ($this->input->post('t_Edad_M')=='')
			$_edad_mes=0;
			else
			$_edad_mes=$this->input->post('t_Edad_M');
		  }
			#Configuración
			//$_SERVER['DOCUMENT_ROOT'];
			//echo $_SERVER['DOCUMENT_ROOT'];
			//echo getcwd(); exit;
			$config['upload_path'] = getcwd().'/uploads';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size'] = '2048';
			$config['max_width'] = '20240';
			$config['max_height'] = '20080';
			//echo $config['upload_path'];
			$this->load->library('upload', $config);
			
			if (!$this->upload->do_upload('t_Foto')) {
            	//$error = array('error' => $this->upload->display_errors());
				//$estable_qry=$this->load->view('observatorio/modulos/encabezadoContenido.php');
				//$this->load->view('observatorio/modulos/mod_nna/registrarNuevo.php',$error);
				$buffer='NULL';
        	} else {
				#Codificamos la imagen en byte para almacenar en la base
				$file_info = $this->upload->data();
				$_fotografia = $file_info['file_name'];
				/*Redimensionamos el archivo antes de guardar */
				$origen=$config['upload_path']."/".$_fotografia;
				$destino=$config['upload_path']."/"."nuevaimagen.jpg";
				$destino_temporal=tempnam("tmp/","tmp");
				$this->redimensionar_transformar_jpeg($origen, $destino_temporal, 300, 300, 100);
				
				// guardamos la imagen reducida 
				$fp=fopen($destino,"w");
				fputs($fp,fread(fopen($destino_temporal,"r"),filesize($destino_temporal)));
				fclose($fp);
				
				/*$fp = fopen($destino, "rb");
				$buffer = fread($fp, filesize($destino));
				fclose($fp);*/
				$data  = file_get_contents($destino);
				
				#convertimos imagen en byte
				$buffer=pg_escape_bytea($data);
				
				#borro la imagen original
				unlink($origen);
				/*$fp = fopen($config['upload_path']."\\".$_fotografia, "rb");
				$buffer = fread($fp, filesize($config['upload_path']."\\".$_fotografia));
				fclose($fp);
				$buffer=pg_escape_bytea($buffer);*/
			}				

			  #Calculo la edad de NNA
			  if ($_fecha_nac!=''){
			  		$this->pcd_edad_nna($_fecha_nac,$_edad_anio,$_edad_mes);
					$_fecha_nac="'".$_fecha_nac."'";
			  }
			  else{
			  	$_fecha_nac='NULL';
			  }
			  //echo $_edad_anio."".$_edad_mes; exit;
			  $_seleccionable=1;
			  $_user_id=$_SESSION['__USER_ID'];
			  $nna_id="00";
			  $data_id = $this->Nnamodel->insertar_nuevo_nna($_app_pat,$_app_mat,
												  $_nombres,$_sexo,
												  $_fecha_nac,$_edad_anio,
												  $_edad_mes,$buffer,
												  $_seleccionable,$_user_id);

			  foreach($data_id -> result() as $linha_id) 
			  { $nna_id = $linha_id->nna_am; }
			  header("Status: 200 OK", true, 200);
			  echo $nna_id;
			  //$url=base_url()."nnacontr/cargar_para_registrar_en_centro_acogimiento/".$data['row'];
			  //redirect($url);
			  /*$estable_qry=$this->load->view('observatorio/modulos/encabezadoContenido.php');
			  $data['registrado_data'] = "Registrado correctamente";
			  $this->load->view('observatorio/modulos/mod_nna/registrarNuevo.php',$data);*/
 
		 }
   }
   /***Funcion que redimensiona y transforma fotografia de NNA***/
    private function redimensionar_transformar_jpeg($img_original, $img_nueva, $img_nueva_anchura, $img_nueva_altura, $img_nueva_calidad)
	{ 
		$info_imagen = getimagesize($img_original);
        $alto = $info_imagen[1];
        $ancho = $info_imagen[0];
        $tipo_imagen = $info_imagen[2];
		switch ($tipo_imagen) {
            case 1: //si es gif …
				// crear una imagen desde el original 
				$img = imagecreatefromgif($img_original); 
				// crear una imagen nueva 
				$thumb = imagecreatetruecolor($img_nueva_anchura,$img_nueva_altura); 
				// redimensiona la imagen original copiandola en la imagen 
				ImageCopyResized($thumb,$img,0,0,0,0,$img_nueva_anchura,$img_nueva_altura,ImageSX($img),ImageSY($img)); 
				// guardar la nueva imagen redimensionada donde indicia $img_nueva 
				ImageJPEG($thumb,$img_nueva,$img_nueva_calidad);
				ImageDestroy($img);
            break;
 
            case 2: //si es jpeg …
				// crear una imagen desde el original 
				$img = ImageCreateFromJPEG($img_original); 
				// crear una imagen nueva 
				$thumb = imagecreatetruecolor($img_nueva_anchura,$img_nueva_altura); 
				// redimensiona la imagen original copiandola en la imagen 
				ImageCopyResized($thumb,$img,0,0,0,0,$img_nueva_anchura,$img_nueva_altura,ImageSX($img),ImageSY($img)); 
				// guardar la nueva imagen redimensionada donde indicia $img_nueva 
				ImageJPEG($thumb,$img_nueva,$img_nueva_calidad);
				ImageDestroy($img);
				
            	break;
 
            case 3: //si es png …
				// crear una imagen desde el original 
				$img = imagecreatefrompng($img_original); 
				// crear una imagen nueva 
				$thumb = imagecreatetruecolor($img_nueva_anchura,$img_nueva_altura); 
				// redimensiona la imagen original copiandola en la imagen 
				ImageCopyResized($thumb,$img,0,0,0,0,$img_nueva_anchura,$img_nueva_altura,ImageSX($img),ImageSY($img)); 
				// guardar la nueva imagen redimensionada donde indicia $img_nueva 
				ImageJPEG($thumb,$img_nueva,$img_nueva_calidad);
				ImageDestroy($img);
            	break;
        }
	}
  /******Lista los NNA registrados en centro de acogida según criterio de selección*******/
	function listar_nna()
	  {
		$this->vld_session();
				  
		if(!isset($_POST["t_AP"]) && 
		!isset($_POST["t_AM"]) && 
		!isset($_POST["t_N"]) && 
		!isset($_POST["t_S"]) && 
		!isset($_POST["t_FN"])){
			$this->load->view('observatorio/modulos/encabezadoContenido.php');
			$this->load->view('observatorio/modulos/mod_nna/listar.php');
			$primera_carga=true;
		}
		else
		{$primera_carga=false;}
		if($primera_carga==false)
		{$this->load->library('table');
	
			  $_app_pat=strtoupper(trim($this->input->post('t_AP')));
			  $_app_mat=strtoupper(trim($this->input->post('t_AM')));
			  $_nombres=strtoupper(trim($this->input->post('t_N')));
			  $_sexo=strtoupper(trim($this->input->post('t_S')));
			  $_fecha_nac=$this->input->post('t_FN');
			  if($_fecha_nac=='')
				$_fecha_nac="1900-01-01";
		$estable_qry = $this->Nnamodel->listar_nna($_app_pat,$_app_mat,$_nombres,$_sexo,$_fecha_nac);
		// generate HTML table from query results
		$tmpl = array (
		  'table_open' => '<table class="bordered" >',
		  'heading_row_start' => '<tr bgcolor="#dddddd">',
		  'row_start' => '<tr bgcolor="#FFFFFF">',
		  'row_alt_start' => '<tr bgcolor="#dddddd">', 
		  );
		$this->table->set_template($tmpl); 
		$this->table->set_empty("&nbsp;"); 
		$this->table->set_heading('Ap. Paterno','Ap. Materno','Nombre (s)','Sexo','Fec. de Nacimiento','Fec. Registro','Fec. Modif','Usuario','Administracion'); 
			
		$table_row = array();
		foreach ($estable_qry->result() as $estable)
		{
		  $table_row = NULL;
		  // replaced above :: $table_row[] = anchor('student/edit/' . $student->id, 'edit');
		  $table_row[] = $estable->nna_app_pat;
		  $table_row[] = $estable->nna_app_mat;
		  $table_row[] = $estable->nna_nombres;
		  $table_row[] = $estable->nna_sexo;
		  if($estable->nna_fecha_nac!=null){
			$date_formated=date_create($estable->nna_fecha_nac);
			$table_row[] = date_format($date_formated,"d/m/Y");
		  }
		  else{
		  $table_row[] ="";}
		  
		  $date_formated=date_create($estable->nna_fec_alta);
		  $table_row[] = date_format($date_formated,"d/m/Y H:i:s");
		  if($estable->nna_fec_mod!=null){
			$date_formated=date_create($estable->nna_fec_mod);
			$table_row[] = date_format($date_formated,"d/m/Y H:i:s");
		  }
		  else{
		  $table_row[] ="";}
		  $table_row[] = $estable->user_codigo;
		  $table_row[] = '<nobr>' . 
			anchor('nnacontr/generar_archivo_pdf/' . $estable->nna_id, 'Imprimir') . ' | ' .
			anchor('nnacontr/cargar_para_modificar_datos_nna/' . $estable->nna_id, 'Editar',
			  "onClick=\" return confirm('Esta seguro que desea modificar el registro '
				+ 'de $estable->nna_app_pat ' + '$estable->nna_app_mat ' + '$estable->nna_nombres?')\"") .
			'</nobr>';//'<img src="'.base_url().'assets/_obs/images/icons/ver.GIF" width="17" height="19" border="0" title="Ver"/>','style="text-decoration:none;cursor:pointer;color:#000000;"')
		  //$table_row[] = mailto($student->email);
		  $this->table->add_row($table_row);
		}    
		$estable_table = $this->table->generate();
		$data['data_table'] = $estable_table;
		print $estable_table;}	
	  }
  /***Lista los NNA para verificación de datos antes de registrar***/
	function listarnna_existentes()
	  {
		$this->vld_session();
				  
		if(!isset($_POST["t_AP"]) && 
		!isset($_POST["t_AM"]) && 
		!isset($_POST["t_N"]) && 
		!isset($_POST["t_S"]) && 
		!isset($_POST["t_FN"])){
			$this->load->view('observatorio/modulos/encabezadoContenido.php');
			$this->load->view('observatorio/modulos/mod_nna/listarnna_existentes.php');
			$primera_carga=true;
		}
		else
		{$primera_carga=false;}
		if($primera_carga==false)
		{$this->load->library('table');
	
			  $_app_pat=strtoupper(trim($this->input->post('t_AP')));
			  $_app_mat=strtoupper(trim($this->input->post('t_AM')));
			  $_nombres=strtoupper(trim($this->input->post('t_N')));
			  $_sexo=strtoupper(trim($this->input->post('t_S')));
			  $_fecha_nac=$this->input->post('t_FN');
			  if($_fecha_nac=='')
				$_fecha_nac="1900-01-01";
		$estable_qry = $this->Nnamodel->listar_nna_existentes_vigentes($_app_pat,$_app_mat,$_nombres,$_sexo,$_fecha_nac);
		if($estable_qry->num_rows()>=1){
		// generate HTML table from query results
		$tmpl = array (
		  'table_open' => '<table class="bordered" >',
		  'heading_row_start' => '<tr bgcolor="#dddddd">',
		  'row_start' => '<tr bgcolor="#FFFFFF">',
		  'row_alt_start' => '<tr bgcolor="#dddddd">', 
		  );
		$this->table->set_template($tmpl); 
		$this->table->set_empty("&nbsp;"); 
		$this->table->set_heading('Ap. Paterno','Ap. Materno','Nombre (s)','Sexo','Fec. de Nacimiento','Fec. Registro','Fec. Modif','Usuario','Administracion'); 
			
		$table_row = array();
		foreach ($estable_qry->result() as $estable)
		{
		  $table_row = NULL;
		  // replaced above :: $table_row[] = anchor('student/edit/' . $student->id, 'edit');
		  $table_row[] = $estable->nna_app_pat;
		  $table_row[] = $estable->nna_app_mat;
		  $table_row[] = $estable->nna_nombres;
		  $table_row[] = $estable->nna_sexo;
		  if($estable->nna_fecha_nac!=null){
			$date_formated=date_create($estable->nna_fecha_nac);
			$table_row[] = date_format($date_formated,"d/m/Y");
		  }
		  else{
		  $table_row[] ="";}
		  
		  $date_formated=date_create($estable->nna_fec_alta);
		  $table_row[] = date_format($date_formated,"d/m/Y H:i:s");
		  if($estable->nna_fec_mod!=null){
			$date_formated=date_create($estable->nna_fec_mod);
			$table_row[] = date_format($date_formated,"d/m/Y H:i:s");
		  }
		  else{
		  $table_row[] ="";}
		  $table_row[] = $estable->user_codigo;
		  $table_row[] = '<nobr>' . 
			anchor('nnacontr/cargar_para_registrar_en_centro_acogimiento/' . $estable->nna_id, 'Reg. Acogimiento');
		  $this->table->add_row($table_row);
		}    
		$estable_table = $this->table->generate();
		$data['data_table'] = $estable_table;
		print $estable_table;}
		else{print 'No se encontro ninguna informaci&oacute;n, <a href="' . base_url().'nnacontr/registro_nna">&iquest;Registrar un nuevo NNA?<a/>';}
		}
		
	  }
	  
  /***Lista los NNA que no estan registrados en un centro de acogimiento***/
	function registronnaespecific()
	  {
		$this->vld_session();
				  
		if(!isset($_POST["t_AP"]) && 
		!isset($_POST["t_AM"]) && 
		!isset($_POST["t_N"]) && 
		!isset($_POST["t_S"]) && 
		!isset($_POST["t_FN"])){
			$this->load->view('observatorio/modulos/encabezadoContenido.php');
			$this->load->view('observatorio/modulos/mod_nna/listar_no_registrados.php');
			$primera_carga=true;
		}
		else
		{$primera_carga=false;}
		if($primera_carga==false)
		{$this->load->library('table');
	
			  $_app_pat=strtoupper(trim($this->input->post('t_AP')));
			  $_app_mat=strtoupper(trim($this->input->post('t_AM')));
			  $_nombres=strtoupper(trim($this->input->post('t_N')));
			  $_sexo=strtoupper(trim($this->input->post('t_S')));
			  $_fecha_nac=$this->input->post('t_FN');
			  if($_fecha_nac=='')
				$_fecha_nac="1900-01-01";
		$estable_qry = $this->Nnamodel->listar_nna_sin_registro($_app_pat,$_app_mat,$_nombres,$_sexo,$_fecha_nac);
		if($estable_qry->num_rows()>=1){
		// generate HTML table from query results
		$tmpl = array (
		  'table_open' => '<table class="bordered" >',
		  'heading_row_start' => '<tr bgcolor="#dddddd">',
		  'row_start' => '<tr bgcolor="#FFFFFF">',
		  'row_alt_start' => '<tr bgcolor="#dddddd">', 
		  );
		$this->table->set_template($tmpl); 
		$this->table->set_empty("&nbsp;"); 
		$this->table->set_heading('Ap. Paterno','Ap. Materno','Nombre (s)','Sexo','Fec. de Nacimiento','Fec. Registro','Fec. Modif','Usuario','Administracion'); 
			
		$table_row = array();
		foreach ($estable_qry->result() as $estable)
		{
		  $table_row = NULL;
		  // replaced above :: $table_row[] = anchor('student/edit/' . $student->id, 'edit');
		  $table_row[] = $estable->nna_app_pat;
		  $table_row[] = $estable->nna_app_mat;
		  $table_row[] = $estable->nna_nombres;
		  $table_row[] = $estable->nna_sexo;
		  if($estable->nna_fecha_nac!=null){
			$date_formated=date_create($estable->nna_fecha_nac);
			$table_row[] = date_format($date_formated,"d/m/Y");
		  }
		  else{
		  $table_row[] ="";}
		  
		  $date_formated=date_create($estable->nna_fec_alta);
		  $table_row[] = date_format($date_formated,"d/m/Y H:i:s");
		  if($estable->nna_fec_mod!=null){
			$date_formated=date_create($estable->nna_fec_mod);
			$table_row[] = date_format($date_formated,"d/m/Y H:i:s");
		  }
		  else{
		  $table_row[] ="";}
		  $table_row[] = $estable->user_codigo;
		  $table_row[] = '<nobr>' . 
			anchor('nnacontr/cargar_para_registrar_en_centro_acogimiento/' . $estable->nna_id, 'Registrar');
		  $this->table->add_row($table_row);
		}    
		$estable_table = $this->table->generate();
		$data['data_table'] = $estable_table;
		print $estable_table;}
		else{print 'No se encontro ninguna informaci&oacute;n, <a href="' . base_url().'nnacontr/registro_nna">&iquest;Registrar un nuevo NNA?<a/>';}
		}
		
	  }
	    
  /*** Mostrar detalles de NNA para ser registrado en centro de acogimiento ***/
     public function cargar_para_registrar_en_centro_acogimiento(){
	  $this->vld_session();
      $centro_acogimiento = $this->Nnamodel->retorna_centros_de_acogimiento($_SESSION['__USER_ID_DEPTO'],$_SESSION['__USER_ID']);
      $option = "<option value=''></option>";
      foreach($centro_acogimiento -> result() as $linha_centros) 
	  { $option .= "<option value='$linha_centros->inst_id'>$linha_centros->inst_nombre</option>"; }
      $data_nna['options_centro_acogimiento'] = $option;

      $deptos = $this->Nnamodel->retorna_departamentos($_SESSION['__USER_ID_DEPTO']);
      $optionDepto = "<option value=''></option>";
      foreach($deptos -> result() as $linha_deptos) 
	  { $optionDepto .= "<option value='$linha_deptos->dep_id'>$linha_deptos->dep_nombre</option>"; }
      $data_nna['options_departamentos'] = $optionDepto;
	  
	  $provincias = $this->Nnamodel->retorna_provincias($_SESSION['__USER_ID_DEPTO']);
      $optionProv = "<option value=''></option>";
      foreach($provincias -> result() as $linha_provs) 
	  { $optionProv .= "<option value='$linha_provs->prov_id'>$linha_provs->prov_nombre</option>"; }
      $data_nna['options_provincias'] = $optionProv;
	  
	  $instituciones = $this->Nnamodel->retorna_institucion_autoriza();
      $option_instOrdena = "<option value=''></option>";
      foreach($instituciones -> result() as $linha_inst) 
	  { $option_instOrdena .= "<option value='$linha_inst->insta_id'>$linha_inst->insta_nombre</option>"; }
	  
	  $data_nna['options_por_orden_de']=$option_instOrdena;
	  
	  $problematic_familiar = $this->Nnamodel->retorna_problematica_familiar("I");
      $optionProbFliar = "";
      foreach($problematic_familiar -> result() as $linha_probFliar) 
	  { $optionProbFliar .= "<option value='$linha_probFliar->ing_egr_id'>$linha_probFliar->ing_egr_nombre</option>"; }
      $data_nna['options_prob_fliar'] = $optionProbFliar;
    
	  $tipologias = $this->Nnamodel->retorna_tipologias_de_ingreso();
      $optionTipolofias = "";
      foreach($tipologias -> result() as $linha_tipologias) 
	  { $optionTipolofias .= "<option value='$linha_tipologias->cat_tip_id'>$linha_tipologias->cat_tip_nombre</option>"; }
      $data_nna['options_tipologias'] = $optionTipolofias;
	
	  /*$categorias = $this->Nnamodel->retorna_categorias_de_ingreso();
      $optioncategorias = "";
      foreach($categorias -> result() as $linha_categorias) 
	  { $optioncategorias .= "<option value='$linha_categorias->cat_tip_id'>$linha_categorias->cat_tip_nombre</option>"; }
      $data_nna['options_categorias'] = $optioncategorias;*/
	
    $id = $this->uri->segment(3);
    $data_nna['row_nna'] = $this->Nnamodel->obtener_datos_nna_x_id($id)->result();
	$this->load->view('observatorio/modulos/encabezadoContenido.php');
    $this->load->view('observatorio/modulos/mod_nna/registrarespecificaciones.php',$data_nna);
  }

  /*** Muestra los datos de registro de un NNA para su modificación*/
  public function cargar_para_modificar_datos_nna(){
	  $this->vld_session();
	  $id = $this->uri->segment(3);
      $data_nna['row_nna'] = $this->Nnamodel->obtener_datos_de_interno_nna($id)->result();
      ##Cargamos datos complementarios
	  foreach($data_nna['row_nna'] as $dat_comp)
	  {   	
		  $centro_acogimiento = $this->Nnamodel->retorna_centros_de_acogimiento($_SESSION['__USER_ID_DEPTO'],$_SESSION['__USER_ID']);
		  $option = "<option value=''></option>";
		  foreach($centro_acogimiento -> result() as $linha_centros) 
		  { 
		  if($dat_comp->inst_id==$linha_centros->inst_id){
			  $select="selected";
		  }else{$select="";}
		  $option .= "<option value='$linha_centros->inst_id'".$select.">$linha_centros->inst_nombre</option>"; }
		  $data_nna['options_centro_acogimiento'] = $option;
	  
		  $deptos = $this->Nnamodel->retorna_departamentos($_SESSION['__USER_ID_DEPTO']);
		  $optionDepto = "<option value=''></option>";
		  foreach($deptos -> result() as $linha_deptos) 
		  { 
			  if($dat_comp->dep_id==$linha_deptos->dep_id){
				  $select="selected";
			  }else{$select="";}
		  $optionDepto .= "<option value='$linha_deptos->dep_id'".$select.">$linha_deptos->dep_nombre</option>"; }
		  $data_nna['options_departamentos'] = $optionDepto;
	  
		  $provincias = $this->Nnamodel->retorna_provincias($dat_comp->dep_id);
		  $optionProv = "<option value=''></option>";
		  foreach($provincias -> result() as $linha_provs) 
		  { 
		  	  if($dat_comp->prov_id==$linha_provs->prov_id){
				  $select="selected";
			  }else{$select="";}
		  $optionProv .= "<option value='$linha_provs->prov_id'".$select.">$linha_provs->prov_nombre</option>"; }
		  $data_nna['options_provincias'] = $optionProv;
	  }
	  /***Traer datos de internacion***/
      $data_nna['row_interna'] = $this->Nnamodel->obtener_datos_de_primera_internacion_de_nna($id)->result();
	  foreach($data_nna['row_interna'] as $dat_comp)
	  { 
		  $instituciones = $this->Nnamodel->retorna_institucion_autoriza();
		  $option_instOrdena = "<option value=''></option>";
		  foreach($instituciones -> result() as $linha_inst) 
		  { 	
		  	if($dat_comp->insta_id==$linha_inst->insta_id){
			  $select="selected";
			}else{$select="";}
			$option_instOrdena .= "<option value='$linha_inst->insta_id'".$select.">$linha_inst->insta_nombre</option>"; }
		  
		  $data_nna['options_por_orden_de']=$option_instOrdena;
	  }
	  #Problematicas no usadas en el regsitro de caso de NNA
	  $problematic_familiar = $this->Nnamodel->retorna_problematica_familiar_no_marcados($id);
      $optionProbFliar = "";
      foreach($problematic_familiar -> result() as $linha_probFliar) 
	  { $optionProbFliar .= "<option value='$linha_probFliar->_ing_egr_id'>$linha_probFliar->ing_egr_nombre</option>"; }
      $data_nna['options_prob_fliar'] = $optionProbFliar;
      
	  #Problematicas usadas en el registro de caso de NNA
	  $problematic_familiar = $this->Nnamodel->retorna_problematica_familiar_marcados($id);
      $optionProbFliar = "";
      foreach($problematic_familiar -> result() as $linha_probFliar) 
	  { $optionProbFliar .= "<option value='$linha_probFliar->_ing_egr_id'>$linha_probFliar->ing_egr_nombre</option>"; }
      $data_nna['options_prob_fliar_marcados'] = $optionProbFliar;
	  
	  #Tipologías de ingreso no usadas en regsitro de caso de nna
	  $tipologias = $this->Nnamodel->retorna_tipologias_de_ingreso_no_marcadas($id);
      $optionTipolofias = "";
      foreach($tipologias -> result() as $linha_tipologias) 
	  { $optionTipolofias .= "<option value='$linha_tipologias->_cat_tip_id'>$linha_tipologias->cat_tip_nombre</option>"; }
      $data_nna['options_tipologias'] = $optionTipolofias;
	
	  #Tipologías de ingreso usadas en regsitro de caso de nna
	  $tipologias = $this->Nnamodel->retorna_tipologias_de_ingreso_marcadas($id);
      $optionTipolofias = "";
      foreach($tipologias -> result() as $linha_tipologias) 
	  { $optionTipolofias .= "<option value='$linha_tipologias->_cat_tip_id'>$linha_tipologias->cat_tip_nombre</option>"; }
      $data_nna['options_tipologias_marcadas'] = $optionTipolofias;
	  
	  #Categorias marcadas en regsitro de caso de nna
	  $categorias = $this->Nnamodel->retorna_categorias_x_nna($id);
	  $cat_nna = $this->Nnamodel->obtener_categorias_interno_nna(0,$id);
	  $valores="";
	  $checked="";
	  foreach($categorias->result() as $fila){
		foreach($cat_nna->result() as $fila_nna){
			if($fila->_cat_id==$fila_nna->cat_id){$checked="checked"; break;}else{$checked="";}
		}		
	  	$valores=$valores."<tr><td><label>
	  	<input type='checkbox' name='chkCategoria[]' value='".$fila->_cat_id."' id='chkCategoria_".$fila->_cat_id."'".$checked."/>".
	  	$fila->_cat_nombre."</label></td></tr>"; 
	  }
	  $valores="<table>".$valores."</table>";
	  $data_nna['check_categorias_marcadas'] = $valores;
	  
	  /*$tipologias = $this->Nnamodel->retorna_tipologias_de_ingreso_marcadas($id);
      $optionTipolofias = "";
      foreach($tipologias -> result() as $linha_tipologias) 
	  { $optionTipolofias .= "<option value='$linha_tipologias->_cat_tip_id'>$linha_tipologias->cat_tip_nombre</option>"; }
      $data_nna['options_tipologias_marcadas'] = $optionTipolofias;*/
	  
	$this->load->view('observatorio/modulos/encabezadoContenido.php');
    $this->load->view('observatorio/modulos/mod_nna/reg_update_nna.php',$data_nna);
  }
   /***Registra un NNA en un establecimiento de acogimiento******/   
   public function registrar_en_centro_de_acogimiento(){
		$this->vld_session();
        $this->form_validation->set_rules('c_cenAcogida','Centro de acogimineto','required');//|min_length[5]|max_length[12]
        $this->form_validation->set_rules('c_DeptoNacimiento','Departamento','required');
	    $this->form_validation->set_rules('c_ProvNacimiento','Provincia','required');
	    $this->form_validation->set_rules('t_LocalidadNacimineto','Localidad','required');
	    $this->form_validation->set_rules('h_nna_id','Informaci&oacute; de NNA','required');
		$this->form_validation->set_rules('c_PorOrdende','Orden de','required');
		$this->form_validation->set_rules('t_FOrden','Fecha de orden','required');
		$this->form_validation->set_rules('t_FHorInter','Fecha hora internacion','required');
		//
		$this->form_validation->set_message('required', '"%s" es requerido.');
		//$this->form_validation->set_message('valid_email', 'Debe escribir una dirección de email correcta');
		//$this->form_validation->set_message('min_length[5]', 'Longitud nnn???');
		//$this->form_validation->set_message('max_length', 'Longitud nnn???');
		//$this->form_validation->set_message('matches', 'Los campos %s y %s no coinciden');
        // 
	   if($this->form_validation->run()==FALSE)
	     {
		  header("Status: 400 Bad Request", true, 400);
		  $data = array(
		  		'h_nna_id' => form_error('h_nna_id'),
                'c_cenAcogida' => form_error('c_cenAcogida'),
                'c_DeptoNacimiento' => form_error('c_DeptoNacimiento'),
				'c_ProvNacimiento' => form_error('c_ProvNacimiento'),
                't_LocalidadNacimineto' => form_error('t_LocalidadNacimineto'),
				't_nna_ci' => form_error('t_nna_ci'),
				'c_PorOrdende' => form_error('c_PorOrdende'),
				't_FOrden' => form_error('t_FOrden'),
				't_FHorInter' => form_error('t_FHorInter'),
            );

            echo json_encode($data); 
  		    exit(1);
	     }
	   else
	     {
			try {
					  $_nna_id=$this->input->post('h_nna_id');
					  $_inst_id=$this->input->post('c_cenAcogida');
					  $_nna_ci=$this->input->post('t_nna_ci');
					  $_dep_id=$this->input->post('c_DeptoNacimiento');
					  $_prov_id=$this->input->post('c_ProvNacimiento');
					  $_nna_localidad=strtoupper(trim($this->input->post('t_LocalidadNacimineto')));
					  $_nna_direc_flia=strtoupper(trim($this->input->post('t_DirecFlia')));
					  $_nna_estado="E";		  #$_nna_estado=strtoupper(trim($this->input->post('t_S')));
					  
					  /*Calculamos edad en base a fecha de nacimiento*/
					  $_fecha_nac=$this->input->post('h_nna_FN');
					  $_nna_edad_ing=$this->input->post('t_EdadI');
					  $_edad_mes=0;
						  if ($_fecha_nac!=''){
								$this->pcd_edad_nna($_fecha_nac,$_nna_edad_ing,$_edad_mes);
						  }
					  $_PorOrdende=$this->input->post('c_PorOrdende');
					  $_FecOrdende=$this->input->post('t_FOrden');
	  				  $_FHorInter=$this->input->post('t_FHorInter');
					  $iniciales=strtoupper(trim($this->input->post('h_iniciales_nna')));
					  $_nna_cod_opcional=strtoupper(trim($this->construye_codigo($_inst_id,$iniciales)));
					  $_nna_observaciones=strtoupper(trim($this->input->post('t_Observ')));
					  $_nna_cert_nac=$this->input->post('rd_CertN');
					  
					  $_user_id=$_SESSION['__USER_ID'];
					  if(!isset($_POST['l_ProvFamiliarList'])){
							$_problematicas=NULL;
					  }
					   else{
					  		$_problematicas=$this->input->post('l_ProvFamiliarList');#$_POST["cerveza"]	
					  }
					  if(!isset($_POST['l_TipologiasList'])){
							$_tipologias=NULL;
					  }
					   else{
					  		$_tipologias=$this->input->post('l_TipologiasList');#$_POST["cerveza"]
					  }
					  
					  if(!isset($_POST['chkCategoria'])){
							$_categorias=NULL;
					  }
					   else{
					  		$_categorias=$this->input->post('chkCategoria');#$_POST["cerveza"]
					  }
  
					  $this->Nnamodel->insertar_interno_nna($_nna_id,
															$_inst_id,
															$_nna_ci,
															$_dep_id,
															$_prov_id,
															$_nna_localidad,
															$_nna_direc_flia,
															$_nna_estado,
															$_nna_edad_ing,
															$_nna_cod_opcional,
															$_nna_observaciones,
															$_nna_cert_nac,
															$_user_id,
															$_problematicas,
															$_tipologias,
															$_categorias,
															$_PorOrdende,
															$_FecOrdende,
															$_FHorInter);#->result()
					header("Status: 200 OK", true, 200);
					$estable_qry=$this->load->view('observatorio/modulos/encabezadoContenido.php');
					$data['registrado_data'] = "Registrado correctamente";
					$this->load->view('observatorio/modulos/mod_nna/registrarespecificaciones.php',$data);
			} catch (PDOException $e) {
				//header("Status: 400 Bad Request", true, 400);
				/*$data = array(
					'err_trans' => $e->getMessage()
            	);

            	echo json_encode($data); 
  		   		exit(1);*/
				//echo "Error inesperado en transacción";//$e->getMessage();
				//exit(1);
			}

 
	  }
   }
   
   /***Actualizar datos de un NNA y datos de acogimiento******/   
   public function modificar_datos_nna_acogimiento(){
		$this->vld_session();
		$this->form_validation->set_rules('t_nombres','Nombre','required');//|min_length[5]|max_length[12]
		$this->form_validation->set_rules('t_fecha_nac','Fec. nac.','callback_validar_fecha');
		$this->form_validation->set_rules('rd_sexo','Sexo','required|callback_validar_sexo');
				//
        $this->form_validation->set_rules('c_cenAcogida','Centro de acogimineto','required');//|min_length[5]|max_length[12]
        $this->form_validation->set_rules('c_DeptoNacimiento','Departamento','required');
	    $this->form_validation->set_rules('c_ProvNacimiento','Provincia','required');
	    $this->form_validation->set_rules('t_LocalidadNacimineto','Localidad','required');
	    $this->form_validation->set_rules('h_nna_id','Informaci&oacute; de NNA','required');
		$this->form_validation->set_rules('c_PorOrdende','Orden de','required');
		$this->form_validation->set_rules('t_FOrden','Fecha de orden','required');
		$this->form_validation->set_rules('t_FHorInter','Fecha hora internacion','required');
		//
		$this->form_validation->set_message('required', '"%s" es requerido.');
		$this->form_validation->set_message('integer', '"%s"  debe ser numero entero.');
		$this->form_validation->set_message('min_length', 'El campo %s debe ser de al menos %s carácteres');

		//$this->form_validation->set_message('valid_email', 'Debe escribir una dirección de email correcta');
		//$this->form_validation->set_message('min_length[5]', 'Longitud nnn???');
		//$this->form_validation->set_message('max_length', 'Longitud nnn???');
		//$this->form_validation->set_message('matches', 'Los campos %s y %s no coinciden');
        // 
	   if($this->form_validation->run()==FALSE)
	     {
		  header("Status: 400 Bad Request", true, 400);
		  $data = array(
		  		'h_nna_id' => form_error('h_nna_id'),
                'c_cenAcogida' => form_error('c_cenAcogida'),
                'c_DeptoNacimiento' => form_error('c_DeptoNacimiento'),
				'c_ProvNacimiento' => form_error('c_ProvNacimiento'),
                't_LocalidadNacimineto' => form_error('t_LocalidadNacimineto'),
				't_nna_ci' => form_error('t_nna_ci'),
				'c_PorOrdende' => form_error('c_PorOrdende'),
				't_FOrden' => form_error('t_FOrden'),
				't_FHorInter' => form_error('t_FHorInter'),
				't_nombres' => form_error('t_nombres'),
				't_fecha_nac' => form_error('t_fecha_nac'),
				'rd_sexo' => form_error('rd_sexo'),
            );

            echo json_encode($data); 
  		    exit(1);
	     }
	   else
	     {
			try {
				#------Datos de NNA--------------------------------	  
				  $_app_pat=strtoupper(trim($this->input->post('t_ap_pat')));
				  $_app_mat=strtoupper(trim($this->input->post('t_ap_mat')));
				  $_nombres=strtoupper(trim($this->input->post('t_nombres')));
				  $_sexo=strtoupper(trim($this->input->post('rd_sexo')));
				  $_fecha_nac=$this->input->post('t_fecha_nac');
				  
				  $_nna_id=$this->input->post('h_nna_id');
				  $_reg_def_id=$this->input->post('h_reg_def_id');
				  $_inst_id=$this->input->post('c_cenAcogida');
				  $_nna_ci=$this->input->post('t_nna_ci');
				  $_dep_id=$this->input->post('c_DeptoNacimiento');
				  $_prov_id=$this->input->post('c_ProvNacimiento');
				  $_nna_localidad=strtoupper(trim($this->input->post('t_LocalidadNacimineto')));
				  $_nna_direc_flia=strtoupper(trim($this->input->post('t_DirecFlia')));
				  $_nna_estado="E";		  
					  
				  /*Calculamos edad en base a fecha de nacimiento*/
				  $_nna_edad_ing=$this->input->post('t_EdadI');
				  $_edad_mes=0;
				  if ($_fecha_nac!=''){
						$this->pcd_edad_nna($_fecha_nac,$_nna_edad_ing,$_edad_mes);
				  }
				  $_PorOrdende=$this->input->post('c_PorOrdende');
				  $_FecOrdende=$this->input->post('t_FOrden');
	  			  $_FHorInter=$this->input->post('t_FHorInter');
				  $iniciales=strtoupper(trim($this->input->post('h_iniciales_nna')));
				  $_nna_cod_opcional=strtoupper(trim($this->construye_codigo($_inst_id,$iniciales)));
				  $_nna_observaciones=strtoupper(trim($this->input->post('t_Observ')));
				  $_nna_cert_nac=$this->input->post('rd_CertN');
				  
				  $_seleccionable=1;
				  $_user_id=$_SESSION['__USER_ID'];
				  //$nna_id="00";	
				  
					#Configuración
					$config['upload_path'] =  getcwd().'/uploads';
					$config['allowed_types'] = 'gif|jpg|png';
					$config['max_size'] = '2048';
					$config['max_width'] = '20240';
					$config['max_height'] = '20080';
					//echo $config['upload_path'];
					$this->load->library('upload', $config);
					
					if (!$this->upload->do_upload('t_Foto')) {
						#Mantener fotografia anterior 
						$datos_nna = $this->Nnamodel->obtener_datos_de_interno_nna($_nna_id);
						foreach($datos_nna->result() as $fila){
							$buffer=$fila->nna_fotografia;
						}
						//$buffer='NULL';
					} else {
						#Codificamos la imagen en byte para almacenar en la base
						$file_info = $this->upload->data();
						$_fotografia = $file_info['file_name'];
						/*Redimensionamos el archivo antes de guardar */
						$origen=$config['upload_path']."/".$_fotografia;
						$destino=$config['upload_path']."/"."nuevaimagen.jpg";
						$destino_temporal=tempnam("tmp/","tmp");
						$this->redimensionar_transformar_jpeg($origen, $destino_temporal, 300, 300, 100);
						
						// guardamos la imagen reducida 
						$fp=fopen($destino,"w");
						fputs($fp,fread(fopen($destino_temporal,"r"),filesize($destino_temporal)));
						fclose($fp);
						
						$data  = file_get_contents($destino);
						
						#convertimos imagen en byte
						$buffer=pg_escape_bytea($data);
						
						#borro la imagen original
						unlink($origen);

					}				
					  $_edad_anio=0;
					  $_edad_mes=0;
					  #Calculo la edad de NNA
					  if ($_fecha_nac!=''){
							$this->pcd_edad_nna($_fecha_nac,$_edad_anio,$_edad_mes);
							$_fecha_nac="'".$_fecha_nac."'";
					  }
					  else{
						$_fecha_nac='NULL';
					  }


					  #-----------------------------------------------------------

					  
					  if(!isset($_POST['l_ProvFamiliarList'])){
							$_problematicas=NULL;
					  }
					   else{
					  		$_problematicas=$this->input->post('l_ProvFamiliarList');	
					  }
					  if(!isset($_POST['l_TipologiasList'])){
							$_tipologias=NULL;
					  }
					   else{
					  		$_tipologias=$this->input->post('l_TipologiasList');
					  }
					  
					  if(!isset($_POST['chkCategoria'])){
							$_categorias=NULL;
					  }
					   else{
					  		$_categorias=$this->input->post('chkCategoria');
					  }
  
					  $this->Nnamodel->actualizar_nna_internacion($_nna_id,
					  										$_reg_def_id,
					  										$_app_pat,
															$_app_mat,
														    $_nombres,
															$_sexo,
														    $_fecha_nac,
															$_edad_anio,
														  	$_edad_mes,
															$buffer,
															$_inst_id,
															$_nna_ci,
															$_dep_id,
															$_prov_id,
															$_nna_localidad,
															$_nna_direc_flia,
															$_nna_estado,
															$_nna_edad_ing,
															$_nna_cod_opcional,
															$_nna_observaciones,
															$_nna_cert_nac,
															$_user_id,
															$_seleccionable,
															$_problematicas,
															$_tipologias,
															$_categorias,
															$_PorOrdende,
															$_FecOrdende,
															$_FHorInter);#->result()
					header("Status: 200 OK", true, 200);
					$estable_qry=$this->load->view('observatorio/modulos/encabezadoContenido.php');
					$data['registrado_data'] = "Registrado correctamente";
					$this->load->view('observatorio/modulos/mod_nna/reg_update_nna.php',$data);
			} catch (PDOException $e) {
				//header("Status: 400 Bad Request", true, 400);
				/*$data = array(
					'err_trans' => $e->getMessage()
            	);

            	echo json_encode($data); 
  		   		exit(1);*/
				//echo "Error inesperado en transacción";//$e->getMessage();
				//exit(1);
			}

 
	  }
   }
   
   /***Generar reporte pdf ***/
    public function generar_archivo_pdf() {
        $this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'Carta', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Emerson Arando Quispe');
        $pdf->SetTitle('SINNA');
        $pdf->SetSubject('Reportes módulo Observatorio');
        $pdf->SetKeywords('TCPDF, PDF, reportes');
 
        // datos por defecto de cabecera, se pueden modificar en el archivo tcpdf_config_alt.php de libraries/config
        #$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 001', PDF_HEADER_STRING, array(0, 64, 255), array(0, 64, 128));
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'SEDEGES - POTOSI', 'SINNA - Observatorio', array(0, 64, 255), array(0, 64, 128));
		$pdf->setFooterData($tc = array(0, 64, 0), $lc = array(0, 64, 128));
 
        // datos por defecto de cabecera, se pueden modificar en el archivo tcpdf_config.php de libraries/config
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
 
        // se pueden modificar en el archivo tcpdf_config.php de libraries/config
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
 
        // se pueden modificar en el archivo tcpdf_config.php de libraries/config
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
 
        // se pueden modificar en el archivo tcpdf_config.php de libraries/config
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
 
        //relación utilizada para ajustar la conversión de los píxeles
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
 
 
		// ---------------------------------------------------------
		// establecer el modo de fuente por defecto
        $pdf->setFontSubsetting(true);
 
		// Establecer el tipo de letra
		 
		//Si tienes que imprimir carácteres ASCII estándar, puede utilizar las fuentes básicas como
		// Helvetica para reducir el tamaño del archivo.
        $pdf->SetFont('helvetica', '', 12, '', true);
 
		// Añadir una página
		// Este método tiene varias opciones, consulta la documentación para más información.
        $pdf->AddPage();
 
		//fijar efecto de sombra en el texto
        $pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));
 
		// Establecemos el contenido para imprimir
        $id_nna = $this->uri->segment(3);#;$this->input->post('provincia');
		$datos_nna = $this->Nnamodel->obtener_datos_de_interno_nna($id_nna);
		foreach($datos_nna->result() as $fila)
		{
			$apellido_pat=$fila->nna_app_pat;
			$apellido_mat=$fila->nna_app_mat;
			$nombres=$fila->nna_nombres;
			$fecha_nac=$fila->nna_fecha_nac;
			$sexo=$fila->nna_sexo;
			if($sexo=='M'){
			$sexo='Masculino';}
			else{$sexo='Femenino';}
			$fotografia="";#$fila->nna_fotografia;
			$centro_acogida=$fila->inst_nombre;
			$CI=$fila->nna_ci;
			$departamento=$fila->dep_nombre;
			$provincia=$fila->prov_nombre;
			$localidad=$fila->nna_localidad;
			$direccionflia=$fila->nna_direc_flia;
			$edad_ingreso=$fila->nna_edad_ing;
			$observaciones=$fila->nna_observaciones;
			$cert_nacimineto=$fila->nna_cert_nac;
			if($cert_nacimineto=='0'){
			$cert_nacimineto='NO';}
			else{$cert_nacimineto='SI';}
			$codigo_opcional=$fila->nna_cod_opcional;
			
		}
		#Recuperamos la fotografía codificada
		$datos_nna = $this->Nnamodel->obtener_fotografia($id_nna);
		
		$sinFoto=true;
		foreach($datos_nna->result() as $fila)
		{
			$imgdata = base64_decode($fila->nna_fotografia);
			$sinFoto=false;
			//echo $imgdata; exit(1);	
		}
		#Si no tiene foto mostramos imagen por defecto
		if ($sinFoto==true){
			$imgdata = base64_decode('iVBORw0KGgoAAAANSUhEUgAAABwAAAASCAMAAAB/2U7WAAAABlBMVEUAAAD///+l2Z/dAAAASUlEQVR4XqWQUQoAIAxC2/0vXZDrEX4IJTRkb7lobNUStXsB0jIXIAMSsQnWlsV+wULF4Avk9fLq2r8a5HSE35Q3eO2XP1A1wQkZSgETvDtKdQAAAABJRU5ErkJggg==');
		}
		
		#REcuperamos datos de internacion
		$reg_fecha_hora="";
		$reg_fecha_orden="";
		$reg_por_ordende="";
		$datos_nna = $this->Nnamodel->obtener_datos_de_primera_internacion_de_nna($id_nna);
		foreach($datos_nna->result() as $fila)
		{
			$reg_fecha_hora=$fila->reg_def_fechahora_reg;
			$reg_fecha_orden=$fila->reg_def_fec_orden;
			$reg_por_ordende=$fila->insta_nombre;			
		}
		// The '@' character is used to indicate that follows an image data stream and not an image file name
		#$pdf->Image('@'.$imgdata);
		$problematica="";
		$cat_result = $this->Nnamodel->obtener_problematica_interno_nna($id_nna,0,0);
		foreach($cat_result->result() as $fila){
			$problematica.=$fila->ing_egr_nombre."<br>&nbsp;";
		}
		
		$tipologias="";
		$cat_result = $this->Nnamodel->obtener_tipologias_interno_nna(0,$id_nna);
		foreach($cat_result->result() as $fila){
			$tipologias.=$fila->cat_tip_nombre."<br>&nbsp;";
		}
		
		$categorias="";
		$cat_result = $this->Nnamodel->obtener_categorias_interno_nna(0,$id_nna);
		foreach($cat_result->result() as $fila){
			$categorias.=$fila->cat_nombre."<br>&nbsp;";
		}
		#Si tiene fotografía se muestra en el codumento
		$pdf->Image('@'.$imgdata, 153, 54, 41, 42, '', '', '', true, 150, '', false, false, 1, false, false, true);
        //preparamos y maquetamos el contenido a crear
        $html = '';
        $html .= "<style type=text/css>";
        $html .= "th{color: #000; background-color: #222}";
        $html .= "td{}";
        $html .= "</style>";
        $html .= "<h2>Registro de NNA en centro de acogia : ".$centro_acogida."</h2>";#<h4>Actualmente: ".$i." localidades</h4>";
        $html .= '<table width="22%" border="0">
  <tr>
    <td>C&Oacute;DIGO</td>
  </tr>
  <tr border="1">
    <td><table width="100%" border="1">
      <tr>
        <td>&nbsp;'.$codigo_opcional.'</td>
      </tr>
    </table></td>
  </tr>
   <tr>
    <td height="5"></td>
  </tr>
</table>
<table border="0" width="100%" height="145">
  <tr>
    <td colspan="4" bgcolor="#408080" style="font:Verdana, Geneva, sans-serif;"><strong>DATOS PERSONALES</strong></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td width="20%" rowspan="6" align="center">'.$fotografia.'</td>
  </tr>
  <tr>
    <td width="26%"><strong>APELLIDO PATERNO</strong></td>
    <td width="26%"><strong>APELLIDO MATERNO</strong></td>
    <td width="28%">&nbsp;<strong>NOMBRES</strong></td>
  </tr>
  <tr>
    <td>'.$apellido_pat.'</td>
    <td>'.$apellido_mat.'</td>
    <td>&nbsp;'.$nombres.'</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><strong>SEXO</strong></td>
    <td><strong>EDAD DE INGRESO</strong></td>
    <td></td>
  </tr>
  <tr>
    <td>&nbsp;'.$sexo.'</td>
    <td>&nbsp;'.$edad_ingreso.'</td>
    <td></td>
  </tr>
</table>
<table border="0">
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
';
		$html .= '<table width="100%" border="0">
  <tr>
    <td colspan="3" bgcolor="#408080" style="font:Verdana, Geneva, sans-serif;"><strong>LUGAR DE NACIMIENTO</strong></td>
  </tr>
  <tr>
    <td><strong>DEPARTAMENTO</strong></td>
    <td><strong>PROVINCIA</strong></td>
    <td><strong>LOCALIDAD</strong></td>
  </tr>
  <tr>
    <td>'.$departamento.'</td>
    <td>'.$provincia.'</td>
    <td>'.$localidad.'</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><strong>FECHA DE NACIMIENTO</strong></td>
    <td><strong>CERTIFICADO DE  NACIMIENTO</strong></td>
    <td><strong>CARNET DE IDENTIDAD</strong></td>
  </tr>
  <tr>
    <td>'.$fecha_nac.'</td>
    <td>&nbsp;'.$cert_nacimineto.'</td>
    <td>'.$CI.'</td>
  </tr>
    <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  </table>
  <table width="100%" border="0">
  <tr>
    <td colspan="3" bgcolor="#408080" style="font:Verdana, Geneva, sans-serif;"><strong>DATOS ESPEC&Iacute;FICOS</strong></td>
    </tr>
  <tr>
    <td width="37%"><strong>CENTRO DE ACOGIDA:</strong></td>
    <td colspan="2"><strong>FECHA Y HORA DE INTERNACI&Oacute;N</strong>:</td>
    </tr>
  <tr>
    <td>'.$centro_acogida.'</td>
    <td>'.$reg_fecha_hora.'</td>
    <td width="26%">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><strong>POR ORDEN DE:</strong></td>
    <td><strong>FECHA EMISI&Oacute;N DE ORDEN:</strong></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="27">'.$reg_por_ordende.'</td>
    <td>'.$reg_fecha_orden.'</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="27">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="27"><strong>DIRECCI&Oacute;N DE FAMILIA:</strong></td>
    <td width="37%"><strong>OBSERVACIONES:</strong></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height="27">'.$direccionflia.'</td>
    <td colspan="2">&nbsp;'.$observaciones.'</td>
    </tr>
</table>';

$html.='<table width="100%" border="0">
  <tr>
    <td width="35%"><strong>PROBLEM&Aacute;TICA FAMILIAR</strong></td>
    <td width="3%"><strong>:</strong></td>
    <td width="62%" rowspan="2">&nbsp;'.$problematica.'</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><strong>TIPOLOG&Iacute;AS DE INGRESO</strong></td>
    <td><strong>:</strong></td>
    <td rowspan="2">&nbsp;'.$tipologias.'</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><strong>CATEGOR&Iacute;AS</strong></td>
    <td><strong>:</strong></td>
    <td rowspan="2">&nbsp;'.$categorias.'</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>';

		// Imprimimos el texto con writeHTMLCell()
        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
 
		// ---------------------------------------------------------
		// Cerrar el documento PDF y preparamos la salida
		// Este método tiene varias opciones, consulte la documentación para más información.
        $nombre_archivo = utf8_decode("reg_".$codigo_opcional.".pdf");
        $pdf->Output($nombre_archivo, 'I');
    }
   
   
   
   /*********************************************************************/
   /**Muestra fotografia de nna**/
   public function mostrar_fotografia(){
	    header("Content-type: image/jpeg");
		$id_nna = $this->uri->segment(3);
		$datos_nna = $this->Nnamodel->obtener_fotografia($id_nna);
		foreach($datos_nna->result() as $fila)
		{
			#$imgdata = $fila->nna_fotografia;
			#$image = imagecreatefromjpeg(pg_unescape_bytea($fila->nna_fotografia));	
			#imagejpeg($fila->nna_fotografia);
			#echo $image;
			#header('Content-type: image/jpeg');
			echo base64_decode($fila->nna_fotografia);	
		}
   }
   /***Carga categorias de ingreso**/
   public function llenar_categorias(){
		$valores="";
		if($this->input->post('tipologias'))
		 {
		    $lista_tipologias=$this->input->post('tipologias');
			if($lista_tipologias!=''){
			$categorias = $this->Nnamodel->retorna_categorias($lista_tipologias);
			foreach($categorias->result() as $fila)
			{$valores=$valores."<tr><td><label>
			<input type='checkbox' name='chkCategoria[]' value='".$fila->cat_id."' id='chkCategoria_".$fila->cat_id."'/>".
			$fila->cat_nombre."</label></td></tr>"; }
			$valores="<table>".$valores."</table>";
			}
		 }
		 echo $valores;
   }
   
    /***Carga categorias de ingreso usados en el registro de un caso de NNA**/
   public function llenar_categorias_x_nna(){
	  $valores="";
	  $checked="";
		if($this->input->post('tipologias') && $this->input->post('id_nna'))
		 {
		    $lista_tipologias=$this->input->post('tipologias');
			$id=$this->input->post('id_nna');
			if($lista_tipologias!=''){
			$categorias = $this->Nnamodel->retorna_categorias($lista_tipologias);
			$cat_nna = $this->Nnamodel->obtener_categorias_interno_nna(0,$id);

			foreach($categorias->result() as $fila){
				foreach($cat_nna->result() as $fila_nna){
					if($fila->cat_id==$fila_nna->cat_id){$checked="checked"; break;}else{$checked="";}
		 		}		
				$valores=$valores."<tr><td><label>
				<input type='checkbox' name='chkCategoria[]' value='".$fila->cat_id."' id='chkCategoria_".$fila->cat_id."'".$checked."/>".
				$fila->cat_nombre."</label></td></tr>"; }
				$valores="<table>".$valores."</table>";
			}
		 }
		 echo $valores;
   }
	public function llenar_provincias(){
		$options="<option value=''></option>";
		if($this->input->post('departamento'))
		 {
			$id_depto = $this->input->post('departamento');
			$provincias = $this->Nnamodel->retorna_provincias($id_depto);
			foreach($provincias->result() as $fila)
			{$options=$options."<option value='".$fila->prov_id."'>".$fila->prov_nombre."</option>"; }
		 }
		echo $options;
	}
	/*** Genera código de nna ***/
	public function genera_codigo_opcional(){
		if($this->input->post('centro_id') && $this->input->post('iniciales'))
		 {	
			$id_centro = $this->input->post('centro_id');
			$iniciales = $this->input->post('iniciales');
			echo $this->construye_codigo($id_centro,$iniciales);
			
		 }
	}
	/***Construye código para exhivir en vista y para ser usado antes de guardar en base de datos ***/
	private function construye_codigo($id_centro,$iniciales){
		$codigo_opcional="";
		if($id_centro!=''){
				
				$instituciones = $this->Nnamodel->retorna_codigo_centro_acogida($id_centro);
				foreach($instituciones->result() as $fila){
				$codigo_opcional=$fila->inst_codigo; 
				$nuevo_valor=$fila->inst_nuevo_valor;
				if($nuevo_valor==null)
				{$nuevo_valor=1;}
				}
				return $codigo_opcional."-".$iniciales."-".str_pad($nuevo_valor,5,"0",STR_PAD_LEFT);
			}
			else{return "";}
	}
}//END OF CLASS
?>
