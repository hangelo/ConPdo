<?php


// tratamento sobre exibir ou não mensagens de excessão/erros

function show_ExceptionMessage( $str ) {
	//echo $str;
	echo 'erro';
	exit;
}


// função que executa rotina em banco de dados para armazenar consultas realizadas pelo usuário

function salva_consulta_db( $sp, $sql, &$_conexao, &$_qry ) {
	global $_LOGIN__usu_id;
	$descricao = $sql."\n\n".$_qry->get_parametros();
	$qry = $_conexao->prepare( 'CALL '.$sp.' ( :usu_id, :descricao );' );
	$qry->bindParam( ':usu_id', $_LOGIN__usu_id);
	$qry->bindParam( ':descricao', $descricao );
	$qry->execute();
}


// função que executa rotina em banco de dados para armazenar consultas realizadas pelo usuário

function salvar_consultas_db( &$_conexao, $evento, $descricao ) {
	global $_LOGIN__usu_id; // variável que armazena ID do usuário logado
	$sql = 'CALL P_HIST_PROC_ADD( :usu_id, :evento, :descricao, @hist_proc_id );'; // função SQL para salvar informação auditada no banco de dados
	$qry = $_conexao->prepare( $sql );
	$qry->bindParam( ':usu_id', $_LOGIN__usu_id );
	$qry->bindParam( ':evento', $evento );
	$qry->bindParam( ':descricao', $descricao);
	$qry->execute();
}


// aloca uma instância de banco de dados e inicia uma transação

function inicia_transacao( &$conexao, &$transaction ) {
	$conexao = conn::getInstance();
	$transaction = new Transaction( $conexao );
}


// faz um comit em uma transação e fecha a conexão ao banco de dados

function commit_transacao( &$conexao, &$transaction ) {
	$transaction->commit();
	$conexao->close();
}


// faz rollback em uma transação e fecha a conexão ao banco de dados

function rollback_transacao( &$conexao, &$transaction, $erro ) {
	$transaction->rollback();
	$conexao->close();
	show_ExceptionMessage( $erro );
	exit;
}


// classe de transação

class Transaction {

  private $db = NULL;
  private $finished = FALSE;

  function __construct($db) {
    $this->db = $db;
    $this->db->beginTransaction();
  }

  function __destruct() {
    if (!$this->finished) {
      $this->db->rollback();
    }
  }

  function commit() {
    $this->finished = TRUE;
    $this->db->commit();
  }

  function rollback() {
    $this->finished = TRUE;
    $this->db->rollback();
  }
}


// classe herdada de PDO->PREPARE
// utilizada no retorno da função PREPARE

class retorno_prepare extends PDOStatement {
	
	private $parametros = array();
	
	private function add_parametro( $nome, $valor, $tipo ) {
		global $parametros;
		$parametros[ count( $parametros ) ] = array( 'nome' => $nome, 'valor' => ( $valor === NULL ? 'NULL' : $valor ), 'tipo' => $tipo );
	}
	
	public function get_parametros() {
		global $parametros;
		$txt = 'Total de parametros: '.count( $parametros )."\n";
		for ( $i = 0; $i < count( $parametros ); $i++ ) {
			$txt .= 'parametro: "'.$parametros[ $i ][ 'nome' ].'"'."\n".'valor: "'.$parametros[ $i ][ 'valor' ].'"'."\n".'tipo: "'.$parametros[ $i ][ 'tipo' ].'"'."\n\n";
		}
		return $txt;
	}
	
	function getPDOConstantType( $var ) { // encontra o tipo da variável e retorna o valor correto para ser passado em BindParam no PDO
		if ( is_int( $var ) ) return PDO::PARAM_INT;
		if ( is_bool( $var ) ) return PDO::PARAM_BOOL;
		if ( is_null( $var ) ) return PDO::PARAM_NULL;
		return PDO::PARAM_STR; // Default 
	}
	
/*
  public function execute($params = array()) {
    return parent::execute($params);
  }
*/
	public function bindParam( $paramno, &$param, $type=null, $maxlen=null, $driverdata=null ) {
		$type = $this->getPDOConstantType( $param );
		$this->add_parametro( $paramno, $param, $type );
		return parent::bindParam( $paramno, $param, $type, $maxlen, $driverdata );
	}

  public function fetchSingle() {
    return $this->fetchColumn(0);
  }

  public function fetchAssoc() {
    $this->setFetchMode(PDO::FETCH_ASSOC);
		$data = $this->fetch();
    return $data;
  }

  public function fetch( $how = NULL, $orientation = NULL, $offset = NULL ) {
    $vr = parent::fetch( $how, $orientation, $offset );
		return $vr;
  }
}


// classe de conexão ao banco de dados iArremate

class conn extends pdo {
		
		/* AMAZON */
		private static $hostname = 	'URL HOSTNAME';
		private static $database = 	'NOME DO DATABASE';
		private static $username = 	'USERNAME';
		private static $password = 	'PASSWORD';

		private static function dns() {
			return 'mysql:host='.( self::$hostname ).';dbname='.( self::$database ).';charset=utf8';
		}
 
    private static $conectado = false; // indica o estado da conexão
    private static $instancia = null; // usado para implementação do design pattern singleton

		public function __construct( $dns, $username, $password ) {
			parent::__construct( $dns, $username, $password );
		}

    public function  __destruct() { // quando o objeto for destruído a conexão é fechada
    	self::$instancia->close();
			self::$instancia = null;
    }

    public function close() { // fecha a conexão sobrescrevendo o método "close" de mysqli
      if ( self::$conectado ) {
      	parent::close();
        self::$conectado = false;
      }
    }
		
    public static function getInstance() { // verifica se já existe na memória uma instância da classe "conexao"
			if ( !isset( self::$instancia ) ) {
				try {
					self::$instancia = new self( self::dns(), self::$username, self::$password );
					self::$instancia->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); 
					self::$instancia->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				}
				catch ( PDOException $e ) {
					die( show_ExceptionMessage( $e->getMessage() ) );
				}
			}
			return self::$instancia; // Se já existe instancia na memória eu a retorno
    }
		

		// consulta que sobrescreve o método da classe ecutando um PREPARE

		public function query($sql, $params = array()) {
			$stmt = $this->prepare($sql);
			$stmt->execute($params);
			return $stmt;
		}		
		
		
		// consulta que sobrescreve o método da classe

		public function prepare( $sql, $options = NULL ) {
				$stmt = parent::prepare($sql, array( PDO::ATTR_STATEMENT_CLASS => array('retorno_prepare') ));
				$stmt->setFetchMode(PDO::FETCH_ASSOC);
        if ( $stmt ) { return $stmt; }
        else throw new Exception( 'Query Exception: '.parent::errorInfo().' numero:'.parent::errorCode() ); // gera uma excessão caso dê algum erro
		}
}


