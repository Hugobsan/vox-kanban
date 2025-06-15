<x-mail::message>
# Bem-vindo ao Vox Kanban, {{ $user->name }}! 🎉

Obrigado por criar sua conta conosco! Estamos muito felizes em tê-lo como parte da nossa comunidade.

O Vox Kanban é uma ferramenta poderosa de gerenciamento de projetos que vai ajudar você e sua equipe a organizarem melhor o trabalho e aumentarem a produtividade.

## O que você pode fazer agora:

- ✅ Criar seus primeiros boards
- ✅ Convidar membros da equipe
- ✅ Organizar tarefas em colunas
- ✅ Atribuir responsáveis às tarefas

<x-mail::button :url="config('app.url')">
Acessar Vox Kanban
</x-mail::button>

Se você tiver alguma dúvida, não hesite em entrar em contato conosco. Estamos aqui para ajudar!

Obrigado,<br>
Equipe {{ config('app.name') }}
</x-mail::message>
