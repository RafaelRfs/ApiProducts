# ApiProducts
API RESTFULL PHP 7 PDO &amp; MYSQL

API Produtos versão 0.5

Tabela banco de dados >> 
Campos>>
id int chave primária
nome varchar permite nulo
descricao text permite nulo
idcategoria int valor padrão 0
idfornecedor int valor padrão 0
qtdestoque int valor padrão 0
preco double valor padrão 0
status int valor padrão 0


API RestFull PHP 7 PDO

Padrão POST >>
http://localhost/apiProducts/

Padrão GET >>
http://localhost/apiProducts/?method=addProducts
http://localhost/apiProducts/?method=listProducts
http://localhost/apiProducts/?method=updateProducts
http://localhost/apiProducts/?method=deleteProducts

Metodos >>
chave = valor
method='addProducts'
method='listProducts'
method='updateProducts'
method='deleteProducts'

Envio de Dados GET/POST >>
Consulta de produtos >> 
  chave = valor
  method='listProducts'

  funções>>

  paginação:  
     limit="LIMIT 0,1000" OU "LIMIT 1000"

  pesquisa por nome do produto:
     search="teste"

  ordenação:
     orderBy="id DESC " OU "ORDER BY id DESC "

  agrupamento:
     groupBy="id, nome" OU "nome"

  clausula WHERE(array):
     where['id'] = 0
     OU
     where['nome'] = "teste"
     OU
     where['descricao'] = " testando"
     OU
     where['id'] = 0
     where['nome'] = "teste"
     where['descricao'] = "testando"

  
inclusão de produtos >> 1 por vez, retorna sempre os dados do produto inserido, como id e etc>>
    method='addProducts'
    where['nome'] = "teste"
    where['descricao'] = "testando"
    where['idcategoria'] = 0  // opcional 
    where['idfornecedor'] = 0 // opcional
    where['qtdestoque'] = 0 // opcional
    where['preco'] = 0 // opcional
    where['status'] = 0 // opcional
   retorna sempre os dados do Db


atualização de produtos >>
    method='updateProducts'
    id= inteiro, valor do id do produto
    data['nome'] = "teste 123"
    campos do Db a ser atualizado


exclusão de produtos
   id= inteiro, valor do id do produto


Retorno de Dados p sua aplicação>>
   Objeto JSON>>
    indices:
     sql: querie sql do banco de dados
     count: contagem por pagina
     count_all: contagem de todos os registros
     data: matriz com os registros encontrados, os nomes dos indices é o nome das colunas da tabela,
     exemplo: data[0]['nome'], data[0]['id']

exemplos GET:
http://localhost/apiProducts/?method=listProducts&search=teste&orderBy=id%20DESC


Arquivos de Log, erros do servidor:
LogDb.txt
