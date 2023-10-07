# Markfy | REST API

## Introdução

Esta API foi desenvolvida usando WordPress com o intuito de desenvolver um sistema que simula um e-commerce com compra e venda de produtos
chamado <strong><a href="https://github.com/GKeslley/markfy">Markfy</a></strong>.

## Recursos
Markfy permite que usuários:
  - Visualizem produtos <br>
  - Postem produtos <br>
  - Comprem produtos <br>
  - Favoritem produtos <br>
  - Comentem em produtos
  - Excluam produtos
  - 
## Requisitos

- PHP 7.4 ou superior
- WordPress 5.8 ou superior
- Banco de dados MySQL

## Instalação

1. Clone este repositório para a sua máquina local.
2. Instale o WordPress na sua máquina local.
3. Configure o banco de dados MySQL.
4. Utilize o <strong><a href="https://github.com/GKeslley/markfy">front-end do projeto</a></strong> para melhor interação. 

## Execução

Inicie o servidor local e acesse o WordPress.

## Teste

Aqui estão alguns exemplos de como você pode testar a API:

### Usuário - Crie uma nova conta, busque outros usuários e atualize suas informações

- **GET /usuario**: Listar informações do usuário logado
- **GET /usuario/{id_usuario}**: Listar informações de outro usuário
- **POST /usuario**: Registro / Login de um usuário
- **PUT /usuario**: Atualizar informações do usuário logado

### Produtos - Consulte produtos cadastrados, cadastre produtos e exclua seus produtos cadastrados

- **GET /produtos**: Listar todos os produtos cadastrados
- **GET /produtos/{categoria}**: Listar todos os produtos de uma categoria especifica cadastrados
- **GET /produtos/{categoria}/{subcategoria}**: Listar todos os produtos de uma categoria e subcategoria especifica cadastrados
- **GET /produtos/{categoria}/{subcategoria}?_user{id_usuario}**: Listar todos os produtos de uma categoria e subcategoria cadastrados por um usuário especifico
- **GET /produto/{slug}**: Listar informações de um produto cadastrado baseado no slug
- **POST /produto**: Postar produto
- **DELETE /produto/{slug}**: Deletar produto

### Favoritos - Favorite produtos que você goste, desfavorite se perdeu o interesse e consulte seus produtos favoritos

- **GET /curtidas**: Listar todos os produtos favoritados
- **GET /curtida/{slug}**: Verificar se o produto está favoritado
- **POST /curtir**: Favoritar um novo produto e desfavoritar caso já esteja como favorito

### Comentários - Comente em postagens para tirar suas dúvidas

- **POST /comentario**: Comentar em postagens

### Transação - Compre produtos e consulte seus produtos comprados

- **GET /transacao**: Listar todos os produtos comprados
- **POST /transacao**: Comprar um novo produto

## Contribuição
Se você deseja contribuir para o projeto, siga as etapas abaixo:

1. Faça um fork deste repositório.
2. Crie uma nova branch com sua contribuição: git checkout -b minha-contribuicao
3. Faça commit das suas alterações: git commit -m 'Adicionando minha contribuição'
4. Envie suas alterações: git push origin minha-contribuicao
5. Abra um pull request.

## Licença
Este projeto está licenciado sob a licença MIT - consulte o arquivo LICENSE.md para obter detalhes.
