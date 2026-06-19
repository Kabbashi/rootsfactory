# Architecture & Data Model

Roots Factory is **workspace-first**: the collaborative team space ships first so members
can work immediately; the public publishing and cited-Q&A layers build on top of the same data.

## Layers

```
┌─────────────────────────────────────────────────────────────┐
│  INSIDE — Team Workspace        (Phase 1, ships first)        │
│  Ideas · threaded Discussion · Topics · Members (SSO)         │
└───────────────┬─────────────────────────────────────────────┘
                │  promote a mature Idea →
┌───────────────▼─────────────────────────────────────────────┐
│  OUTSIDE — Public Knowledge     (Phase 2)                    │
│  Publications (SEO pages, DE/EN, PDF) · Authors · Topics     │
└───────────────┬─────────────────────────────────────────────┘
                │  grounded answers →
┌───────────────▼─────────────────────────────────────────────┐
│  Q&A WITH SOURCES               (Phase 3)                    │
│  KnowledgeChunk (pgvector) · cited research sources          │
└─────────────────────────────────────────────────────────────┘
```

## Data model (MVP — Phase 1 in **bold**)

| Model | Purpose | Key fields |
|---|---|---|
| **Member** | A team user, synced via conceptnote SSO | `name`, `email`, `role` (member/editor/admin), `sso_subject` |
| **Idea** | The unit of thinking — a note/post in the workspace | `member_id`, `title`, `body` (markdown), `status` (draft/in_discussion/published), `topic_id`, `pinned` |
| **Comment** | Threaded discussion on an Idea | `idea_id`, `member_id`, `parent_id` (nullable, for threads), `body` |
| **Topic** | Taxonomy shared by ideas & publications | `name`, `slug`, `description` |
| **Attachment** | File/source attached to an Idea | `idea_id`, `path`, `title`, `kind` (file/link/pdf) |
| Publication | A curated public piece (often promoted from an Idea) | `idea_id` (nullable), `title`, `slug`, `abstract`, `body`, `language`, `published_at`, `pdf_path` |
| PublicationAuthor | n:m Publication ↔ Member | — |
| KnowledgeChunk | Embedded text chunk for Q&A (pgvector) | `source_type`, `source_id`, `content`, `embedding vector` |
| QaQuery | Log of public questions + cited answers | `question`, `answer`, `citations` (json) |

## Surfaces

- **Workspace (members):** a focused Filament panel (or Livewire UI) — feed of Ideas, open one,
  discuss in a thread, change status, attach sources. This is what the team uses daily.
- **Public site (Blade):** publication list + detail pages, SEO/sitemap/RSS, DE/EN via `__()`.
- **Admin (Filament):** editorial moderation, topics, members, publishing.

## Integrations (reuse, don't rebuild)

- **SSO:** passwordless OIDC from conceptnote (same pattern already used for Odoo/ERPNext/Superset).
- **Research/Q&A engine:** port the public-API source classes from laragent
  (`OpenAlexSource`, `ArxivSource`, `CrossRefSource`, `SemanticScholarSource`) into an open,
  pluggable module; add development-cooperation sources (OECD/DAC, World Bank, EU evaluations).
- **AI:** Laravel AI SDK — Ollama default (`nomic-embed-text` for embeddings, local & free),
  OpenAI optional via `.env`. No keys committed.

## Roadmap

- **Phase 1 — Workspace (now):** Member/Idea/Comment/Topic/Attachment + SSO + member UI. Team works immediately.
- **Phase 2 — Publish outward:** Publication from Idea, public SEO pages, newsletter/RSS.
- **Phase 3 — Cited Q&A:** pgvector knowledge base, ported research sources, public Q&A with citations.
- **Phase 4 — Ecosystem:** funding/trend intelligence bridge to artinos (EU-TED), shared expert pool.
