# Roots Factory 🌱🏭

**An open, AI-assisted think-tank for development cooperation — built workspace-first.**

Roots Factory is a small think-tank platform with two layers:

- **Inside — Team workspace:** capture ideas, discuss in threads, share drafts & sources.
- **Outside — Public knowledge:** publish mature work as briefs/studies with **cited** AI Q&A.

> The idea came from the conceptnote team's wish to discuss and share research among themselves and with others. See [`CONCEPT.md`](CONCEPT.md).

## Status

🚧 **Early MVP — workspace-first.** The team workspace is built first so members can start working immediately; the public publishing + cited Q&A layers build on top.

See the roadmap and data model in [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Stack

- **Laravel** (PHP 8.5) · **Filament** admin · **PostgreSQL** (+ `pgvector` for the Q&A knowledge base)
- **Laravel AI SDK** — Ollama by default (local, no API key required), OpenAI optional via `.env`
- **SSO** — passwordless OIDC from the conceptnote identity, so the team needs no second account
- **Research/Q&A** — cited answers from open academic sources (OpenAlex, arXiv, CrossRef, Semantic Scholar) + development-cooperation sources

## Getting started

```bash
git clone https://github.com/Kabbashi/rootsfactory.git
cd rootsfactory
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

> AI runs locally on Ollama out of the box — no keys needed to try it.

## License

[MIT](LICENSE) — use it, fork it, build on it.

## Contributing

Issues and PRs welcome — especially new development-cooperation data sources for the research engine. Please keep secrets out of the repo (`.env` is git-ignored).
