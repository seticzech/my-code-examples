# Příklady mé práce

## Symfony:

Celý projekt představuje multi-tenant modulární systém
pro vývoj webových aplikací 3. stran, jeho součástí jsou nezávislí frontend klienti a konfigurovatelná
sdílená administrace. O práci s daty a databází se stará ORM Doctrine 2, data jsou uložena v PostgreSQL.

## Nette:

Ukázky zdrojových kódů pro [Nette framework](https://nette.org)

- **Import.php**: Doctrine entita
- **ReportPresenter.php**: REST API presenter
- **Reproduce.php**: Symfony Console command
- **ReportPresenter.phpt**: test REST API presenteru v Nette Testeru
- **nette_app**: malá a jednoduchá aplikace pro zadávání nabídek; Doctrine a lokální SQLite

## Phalcon:

Ukázky zdrojových kódů pro [Phalcon framework](https://phalcon.io/en-us). 
Ukázky pochází z kompletního refaktoringu jednoho z největších e-shopů s obuví [Světbot.cz](https://svetbot.cz)

- **Kupnajisto.php**: data model pro platební systém KupNajisto
- **PosmerchantApi.php**: data model pro platební systém ČSOB POS Merchant; platby debetní/kreditní kartou
- **Payment.php**: formulář pro editaci platebního systému v administraci
- **PaymentController.php**: controller pro správu platebních systémů v administraci

## Zend:

Ukázky zdrojových kódů pro [Zend framework](https://framework.zend.com)

- **Inodes.php**: model pro tabulku záznamů virtuálního souborového systému
- **Locales.php**: model pro tabulku národních prostředí
- **zend_app**: ukázka MVC aplikace, včetně konfigurace, modulů, layoutu, JS, CSS

## Laravel:

Ukázky zdrojových kódů pro [Laravel framework](https://laravel.com)

Výběr zdrojových souborů z API. Pro projekt byl místo návrhového vzoru MVC zvolen
návrhový vzor [Action-Domain-Responder](https://en.wikipedia.org/wiki/Action%E2%80%93domain%E2%80%93responder). Každému URL odpovídá právě jedna akce
a právě jedna domain service. Responder je pak zodpovědný za vytvoření požadovaného
formátu návratových dat. Z toho důvodu je celá aplikace mnohem přehlednější
a lépe testovatelná. O práci s daty a databází se stará ORM Doctrine 2.

## Vue:

Výběr zdrojových souborů frameworku Vue.JS z frontendové client-server aplikace využívající REST API.
O formátování prvků na stránce se stará Bootstrap 4, respektive [Bootstrap Vue](https://bootstrap-vue.org/),
což je projekt spojující knihovnu Bootstrap s Vue aplikací bez použití knihovny jQuery. 
O AJAX komunikaci se stará [axios](https://github.com/axios/axios), 
o správu lokálních dat pak [vuex store](https://vuex.vuejs.org).