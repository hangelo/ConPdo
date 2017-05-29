<?php

try {


	// cria conexão com banco de dados e já inicia transação

	inicia_transacao( $conexao, $transaction );


	// executa uma SQL

	$sql = 'CALL MINHASP( :usu_id );';
	$qry = $conexao->prepare( $sql );
	$qry->bindParam( ':usu_id', $usu_id );
	$qry->execute();


	// armazena a execução acima, já com os parâmetros e valores salvos

	salva_consulta_db( 'P_SALVA_SQL', $sql, $conexao, $qry );


	// commita a transação e já fecha conexão com banco de dados

	commit_transacao( $conexao, $transaction );


} catch ( Exception $e ) {


	// em caso de erro, faz o rollback no banco de dados e fecha transação.
	// também faz o tratamento do erro, no caso, exibe uma mensagem de erro ao usuário.
	// o erro é conhecido, mas pode ser tratado antes, ou simplesmente ocultado.

	rollback_transacao( $conexao, $transaction, $e->getMessage() );
}
