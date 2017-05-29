# conpdo
Classes que melhoram a já existente PDO para conexão à banco de dados.

Classe ConPDO
------------
Este é um conjunto de classes e funções que sobreescrevem ou adicionam rotinas à classe de manipulação de banco de dados PDO.

A intenção deste projeto foi o de facilitar o código e também liberar determinas rotinas automáticas.

Ao se conectar ao banco de dados, uma transação será automaticamente criada. O sistema controla se outra conexão já estava ativa no momento, simplesmente reaproveitando a conexão já aberta.

Normalmente será usada a função "prepare". O sistema armazena o SQL de conexão em uma outra classe derivada de PDOStatement. Esse controle possibilita tratar o SQL e os parâmetros passados, criando assim um sistema eficiente de auditoria de forma totalmente automática. Além disso, essa segunda classe define o tipo de valor à ser passado por "bindParam", removendo a necessidade do programador de fazer isso toda vez.

Caso se queira usar a função "query", o sistema faz a conversão internamente para que sempre seja usado "prepare" de forma transparente.

O controle de erros é feito através da função "rollback_transacao". Dentro dessa é feito uma chamada à uma função que se encarrega de tratar a mensagem de erro, seja executando outra rotina, exibindo o erro em tela ou simplesmente ocultando-o. Dessa forma o programador não precisa se preocupar em lembrar onde em seu sistema ele pode ter esquecido alguma depuração em aberto.

Exemplo de Uso
------------

#### Para carregar

```php
require_once( 'conpdo.class.php' );
```

#### Para iniciar uma conexão

```php
try {
  inicia_transacao( $conexao, $transaction );
```

#### Para executar uma chamada SQL

```php
	$sql = 'CALL MINHASP( :usu_id );';
	$qry = $conexao->prepare( $sql );
	$qry->bindParam( ':usu_id', $usu_id );
	$qry->execute();
```

"MINHASP" pode ser uma stored procedure. Funciona da mesma forma também para consultas com SELECT ou INSERT.

A chamada "bindParam" entende o tipo de parâmetro passado, não necessitando o programador informar a todo instante. Essa etapa também já está em processo o controle do que está sendo passado como parâmetro, o tipo de variável e valor dessa variável, para o sistema de auditoria.

Por fim "execute" onde efetivamente executará o SQL.

#### Uso do sistema de auditoria

```php
salva_consulta_db( 'P_SALVA_SQL', $sql, $conexao, $qry );
```

Essa função possui como primeiro parâmetro a stored procedure responsável por armazenar em banco de dados a informação auditada.

#### Fechar conexão e tratar erros

```php
commit_transacao( $conexao, $transaction );
} catch ( Exception $e ) {
  rollback_transacao( $conexao, $transaction, $e->getMessage() );
 }
 ```

"Commit" consolidará o resultado da transação no banco de dados.

Se houver erros na execução, este será tratado após o "catch". A função "rollback_transacao" se encarregará de dar o rollback, fechar a conexão e exibir o erro em tela, tratá-lo ou simplesmente esconde-lo.

Dessa forma o programador pode usar essa função para depurar erros SQL, ficando tranquilo com todo o sistema pois não precisará se lembrar de substituir funções de depuração em todo o seu sistema.
