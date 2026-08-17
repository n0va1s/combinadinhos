# Diretrizes de Desenvolvimento

Ao desenvolver ou modificar código neste projeto, as seguintes regras e diretrizes devem ser estritamente seguidas:

## Stack Tecnológico
- **Livewire & Volt:** Utilize sempre o Laravel Livewire com a API do Volt para criar componentes funcionais e de página.
- **Interface (UI):** Utilize sempre componentes **Flux** para a construção da interface do usuário.

## Padrões de Variáveis
- **Idioma:** Todas as variáveis devem ser nomeadas em Português do Brasil (pt-BR).
- **Semântica:** Use nomes semânticos e descritivos para variáveis. O nome deve refletir claramente a finalidade do dado (ex: evite `x` ou `dados`, prefira nomes que expliquem o conteúdo).
- **Formatação:** Utilize o padrão `camelCase` para o nome de todas as variáveis (ex: `quantidadeUsuarios`, `valorTotalPedido`).

## Arquitetura e Regras de Negócio
- **Separação de Responsabilidades (Actions/Services):** Evite colocar regras de negócio complexas, consultas pesadas ao banco de dados ou chamadas de API externas diretamente dentro dos componentes Volt/Livewire. O Volt deve focar na interface e reatividade. Lógicas pesadas devem ser extraídas para classes de serviço (Services) ou Actions.

## Estilização e Design System
- **Uso do Flux sobre Tailwind:** Antes de criar classes utilitárias customizadas do Tailwind CSS em um elemento, verifique se o **Flux** já não oferece uma propriedade nativa no componente (ex: prefira usar propriedades do componente Flux em vez de estilizar tags HTML comuns na mão com Tailwind). Isso mantém a consistência visual do projeto e reduz a duplicação de código.

## Textos e Traduções
- **Internacionalização (pt-BR):** Mantenha mensagens de validação, textos de botões e retornos de erro sempre em pt-BR. Se estiver utilizando o pacote de traduções nativo, dê preferência para a função `__()` em mensagens padronizadas.

## Acessibilidade
- **Padrão WCAG AAA:** Todas as interfaces devem ser construídas garantindo conformidade com o nível AAA das Diretrizes de Acessibilidade para Conteúdo Web (WCAG). Isso inclui o uso adequado de contraste de cores, suporte completo à navegação por teclado, rótulos adequados e suporte a leitores de tela em todos os componentes.
