# CN | CN Editor

## About the CN Project

**The goal of the "Corpus Nummorum" (CN) is systematically to collect and publish all ancient coins of lower Moesia, Mysia, Thrace, and the Troad.**  

[Corpus Nummorum](https://www.corpus-nummorum.eu) Online is a web portal devoted to the ancient coins of lower Moesia, Thrace, Mysia and the Troad. This is a pilot project for assembling ancient Greek coinage by region and mint for the various purposes of research and cultural heritage preservation.  

The research database is based primarily on Berlin collections, which include coins from more than 100 mints from the aforementioned regions in the [Münzkabinett Berlin](https://www.smb.museum/en/museums-institutions/muenzkabinett/home/), as well as an extensive collection of plaster casts that were made from coins in various collections worldwide and deposited at the [Berlin-Brandenburg Academy of Sciences and Humanities (BBAW)](https://www.bbaw.de/en/). These datasets will be further supplemented by digital museum catalogues and material from other sources. In line with the concept of public science, the portal also offers the possibility of augmenting the database by registering coins externally. The database makes it possible to sort individual coins systematically and group them by mints and types, as well as by dies that were used in the minting process. If needed, types can also be subdivided or arranged into larger groups, such as series or issues. All coins in the portal are scientifically described in both German and English. Standardised criteria for the description of coin images, both for coin types and individual specimens, have been developed (for the description guidelines, click here or use the Help button).  

As a collective endeavour of the Berlin-Brandenburg Academy of Sciences and Humanities, the Münzkabinett der Staatlichen Museen zu Berlin, and the [Big Data Lab of Goethe University in Frankfurt am Main](http://www.bigdata.uni-frankfurt.de/), the portal is being developed in close collaboration with other international initiatives for the typological presentation of Greek coins in the Semantic Web, such as the Online Greek Coinage project, which is under the patronage of the [International Numismatic Council](https://www.greekcoinage.org/). All relevant database fields are linked to stable Uniform Resource Identifiers (URIs) of numismatic concepts [Nomisma](http://nomisma.org). Because the portal is funded through various external-grant projects, there is variation in the scope of the data and the depth of coverage, but the overarching goal remains consistent: to create type catalogues for each respective coinage.

## About the CN Editor App

The [CN Editor](https://data.corpus-nummorum.eu) provides a standardised and convenient way to enter, search and evaluate coins, types, dies and related data (description, origin, etc.). It is one of the most comprehensive, accessible and performant tools in numismatics available. It is comparatively lightweight and highly modular.  

The backend consists of a MySQL database and a PHP JSON API. The frontend is a Vue.js single page application.

The CN Editor was tailored to the specific needs of the CN. In order to be able to use the software directly for other projects, the same basic structure must be used as for the CN (this applies in particular to the database). Otherwise, it is advisable to create a fork and adapt the app at code level. With future updates we will generalise the editor more to make it easier for other projects to use it directly. However, all currently planned features must be implemented first.  
In any way, please feel free to reuse the code for your personal projects as you wish.

## Installation and Usage

For instructions on installing and using the app, check [DOCUMENTATION.md](https://github.com/telota/corpus-nummorum-editor/blob/github/).

## Main Dependencies

* [Laravel ^8.0](https://laravel.com/)
* [PHP 8.0](https://www.php.net/)
* [MySQL 5.6](https://www.mysql.com/)
* [Vue.js 2.6](https://vuejs.org/)
* [Vue Router 3.0.1](https://router.vuejs.org/)
* [Vuex 3.0.1](https://vuex.vuejs.org/)
* [Vuetify 2.4.6](https://vuetifyjs.com/en/)

## Realization and Licensing

[Berlin-Brandenburg Academy of Sciences and Humanities](https://www.bbaw.de/)   
[TELOTA - IT/DH](https://www.bbaw.de/en/bbaw-digital/telota)   
2020-2021

The CN Editor App is open-sourced software licensed under the [GPLv3](http://www.gnu.org/licenses/gpl-3.0.en.html)   
created by [Jan Köster](https://orcid.org/0000-0003-2713-5207) and supported by [Claus Franke](https://orcid.org/0000-0003-3371-6163) as database architect.