# HuMo-genealogy


**Free & open‑source web‑based genealogy software**.

Download the current version: [github.com/HuubMons/HuMo-genealogy/releases](https://github.com/HuubMons/HuMo-genealogy/releases).


## Screenshot

![HuMo-genealogy screenshot](docs/assets/HuMo-genealogy_home.png)

## Links

- [Website](https://huubmons.github.io/HuMo-genealogy/)

- [Demo website](https://humo-gen.com/humo-gen/)

- [Report bugs and issues](https://github.com/HuubMons/HuMo-genealogy/issues)  
  Use the issue tracker to report bugs, broken behavior, or other problems.

- [Requests and remarks](https://github.com/HuubMons/HuMo-genealogy/discussions)  
  Use the discussion forum for questions, suggestions, and general feedback about the project.

- [Documentation](https://huubmons.github.io/HuMo-genealogy/documentation.html)

- [Old PDF documentation](https://sourceforge.net/projects/humo-gen/files/HuMo-gen_Manual/)

## Public URL configuration

HuMo-genealogy normally derives its public origin from the current request. To
use a fixed, trusted origin for generated links, including password-reset URLs,
set an environment variable such as:

```text
HUMOGEN_PUBLIC_ORIGIN=https://genealogy.example.com:8443
```

The value may contain an HTTP or HTTPS scheme, a hostname or IPv6 address, and
an optional port. It must not contain a path, query, fragment, or credentials.

When HuMo-genealogy is only reachable through a trusted reverse proxy, forwarded
host and protocol headers can be enabled with:

```text
HUMOGEN_TRUST_PROXY_HEADERS=true
```

Do not enable this setting when clients can connect directly to the application
and supply their own forwarded headers.
