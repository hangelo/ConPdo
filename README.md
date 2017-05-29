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
