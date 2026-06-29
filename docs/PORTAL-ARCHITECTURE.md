# Roots Factory — Portal Architecture

*An independent international think tank: a calm, academic, collaborative space for
qualitative research, joint data analysis and shared scientific writing.*

This document is the information architecture (IA) for the portal. It is the reference
the implementation follows. The system is **workspace-first**: members work inside a
private collaborative space; mature work is published outward as open, source-backed
scholarship.

> **Tone:** open, calm, professional, academic. No startup atmosphere, no corporate
> language, no sales or investor framing.

---

## 1. Navigation structure

### Public site (Blade, open to everyone)

| Area | Purpose |
|---|---|
| **Home** | Latest research, new publications, ongoing projects, latest discussions, editorial notes |
| **About** | Mission, vision, values, methodological approach, team, network, contact |
| **Research** | Public project list + project detail (description, countries, team, methodology, findings, publications) |
| **Publications** | List + detail (abstract, authors, citation, DOI/identifier, downloads, version) |
| **Community** | Member directory + read-only public profiles |
| **Ask** | Cited Q&A grounded in published work (`/ask`) |

### Member workspace (Filament panel at `/workspace`, navigation groups)

1. **Overview** — calm academic dashboard: my projects, my tasks, publications in review, recent discussions, editorial notes.
2. **Research** — Research Projects · Research Archive (archived filter) · Concept Notes.
3. **Publications** — Publications with versioning, authors, review status, DOI, citation, downloads, comments.
4. **Collaborative Workspace** — per-project space: documents (versioned), tasks, discussions, references/literature, attachments.
5. **Data Hub** (qualitative) — data items (transcripts, focus groups, field notes, observations, documents, media), codes, categories, themes, codings, evidence.
6. **Knowledge Library** — methods, interview guides, instruments, frameworks, case studies, literature, handbooks, templates.
7. **Community** — members with rich scholarly profiles.
8. **Editorial Office** — manage the publication workflow + reviews.
9. **Help & Taxonomy** — FAQ, research topics, regions/countries.
10. **AI Assistant** — research assistance only; never makes scholarly decisions.

---

## 2. User roles & permissions

### Global roles (`users.role`)
| Role | Who | Can |
|---|---|---|
| `researcher` (default) | Researchers, consultants, PhD students | Create/join projects, contribute data, draft concept notes & publications |
| `author` | Authors of record | Everything a researcher can + lead authorship on publications |
| `reviewer` | Peer reviewers | Conduct assigned internal/peer reviews |
| `editor` | Editorial office / editors | Drive the editorial workflow, publish, manage taxonomy |
| `admin` | Maintainers | Everything, incl. member & system administration |
| `system` | AI author identity | Posts AI comments only; never logs in |

`User::isEditor()` = editor|admin (exists). Add `isReviewer()` and `isStaff()` helpers as needed.

### Project-level roles (pivot `project_user.role`)
`lead` · `member` · `reader` — scope what a member may do inside a specific project workspace.

### Authorship roles (pivot `publication_author.role`)
`author` · `co_author` · `editor` · `guest` (guest authors / columnists).

### Permission approach
Filament gates on `isEditor()`/`isReviewer()` for editorial and review actions; project
visibility scoped by membership. Fine-grained Laravel policies are a follow-up.

---

## 3. Page hierarchy

```
Public
├── /                     Home
├── /about                About
├── /research             Research projects (list)
│   └── /research/{slug}  Project detail
├── /publications         Publications (list)         ← also "/" front matter
│   └── /publications/{slug}
├── /people/{slug}        Member profile (Community)
└── /ask                  Cited Q&A

Workspace (/workspace, authenticated)
├── Overview (dashboard)
├── Research / Research Projects (+ archive filter) / Concept Notes
├── Publications  (→ versions, authors, reviews)
├── Research project detail = the collaborative workspace
│     └── documents · tasks · discussions · references · data items · attachments
├── Data Hub / Data Items · Codes · Categories · Themes
├── Knowledge Library
├── Community / Members
├── Editorial Office
├── Help & Taxonomy / FAQ · Topics · Regions
└── AI Assistant
```

---

## 4. Data model

Shared taxonomy reused everywhere: **Topic** (research themes) and **Region** (countries).
Discussion reuses the existing **polymorphic `comments`** table; files reuse **`attachments`**.

| Model | Purpose | Key fields |
|---|---|---|
| **ResearchProject** | The unit of collaborative research | `title`, `slug`, `kind` (project/field_study/baseline/evaluation/policy_research), `summary`, `objectives`, `methodology`, `research_questions`, `data_collection`, `findings`, `status` (planned/active/completed/archived), `start_date`, `end_date`, `lead_user_id` |
| ProjectUser (pivot) | Team membership | `project_id`, `user_id`, `role` (lead/member/reader) |
| project_topic / project_region | Themes & countries | — |
| **Publication** | A scholarly output | `title`, `slug`, `type` (working_paper/research_paper/policy_brief/strategy_paper/report/critical_column/essay), `abstract`, `body`, `language`, `status` (editorial stages), `doi`, `citation`, `published_at`, `downloads`, `project_id?`, `current_version_id?` |
| PublicationVersion | Document history | `publication_id`, `version_no`, `abstract`, `body`, `changelog`, `created_by` |
| publication_author (pivot) | n:m authors | `publication_id`, `user_id`, `order`, `role` |
| **Review** | Editorial / peer review | `publication_id`, `reviewer_id`, `stage` (internal/peer), `status` (pending/in_progress/done), `recommendation`, `comments`, `due_at` |
| **Task** | Project to-do | `project_id`, `title`, `description`, `assignee_id`, `status` (todo/doing/done), `due_at` |
| **Reference** | Literature / bibliography | `project_id?`, `title`, `authors`, `year`, `source`, `url`, `doi`, `citation_key`, `notes` |
| **ProjectDocument** (+ versions) | Collaborative writing | `project_id`, `title`, `body` (markdown), `created_by`; versions mirror body history |
| **DataItem** | Qualitative datum | `project_id?`, `kind` (transcript/focus_group/field_note/observation/document/media), `title`, `content`, `path`, `language`, `collected_at`, `source_meta` (json) |
| **Theme → Category → Code** | Coding frame (hierarchical) | `name`, `description`, parent fk, `color` (code) |
| coding (pivot) | Apply a code to a datum | `data_item_id`, `code_id`, `excerpt` |
| **Document** (evolved) | Knowledge Library entry | + `kind` (method/guide/instrument/framework/case_study/literature/handbook/template) |
| **User** (evolved) | Scholarly profile | + `expertise`, `country_experience`, `languages`, `method_competencies` (json) |
| Idea → **Concept Note** (relabelled) | Lightweight early thinking | unchanged schema; UI label only |

---

## 5. Project structure (the collaborative workspace)

Each `ResearchProject` is a self-contained workspace. In Filament it is the project's
**Edit** page with relation managers:

- **Team** — members + project role.
- **Documents** — collaborative writing with version history and threaded comments.
- **Tasks** — to-dos with assignee, status, due date.
- **Discussions** — the polymorphic comment thread on the project.
- **References** — literature/bibliography for the project.
- **Data Items** — the project's qualitative evidence (links into the Data Hub).
- **Attachments** — files/links.

Project metadata captures the full research design: objectives, methodology, research
questions, data-collection plan, findings, countries, timeline.

---

## 6. Workflows

### Research lifecycle
```
planned → active → completed → archived
```
A project gathers team, methodology, Data Hub items, documents, tasks and references,
and produces one or more Publications.

### Publication / editorial workflow (`publications.status`)
```
draft → internal_review → peer_review → revision → copy_edit → approved → published
                                                              ↘ archived
```
- Each `internal_review`/`peer_review` stage is backed by `reviews` (assigned reviewers,
  recommendation, comments).
- The **Editorial Office** drives transitions (editor/admin), sees publications by stage
  and the open reviews assigned to the current user.
- Every save can snapshot a **PublicationVersion** (history + changelog).

---

## 7. Dashboard concept (Overview)

A quiet, text-forward dashboard — not a metrics wall:
- **My projects** (where I am lead/member), most recently active.
- **My tasks** (open, by due date).
- **Publications in review** (where I am author or assigned reviewer).
- **Recent discussions** across my projects.
- **Editorial notes** (pinned messages from the editorial office).

---

## 8. Document management

- Collaborative project documents are **versioned** (async, not real-time): each meaningful
  save can create a `ProjectDocument` version with a changelog; history is browsable and
  comparable. Comments thread on the document.
- Publications carry their own version chain (`PublicationVersion`) plus DOI/citation.
- Files (attachments, data files, library items) use Laravel storage; an S3-compatible
  disk is a configuration change, not a rewrite.

---

## 9. Community functions

- Member directory with scholarly profiles: biography, expertise, country experience,
  languages, methodological competencies, publications, projects.
- Profiles are read-only public for members with published work; full profile editable
  by the member and by admins.
- Framed as a **scholarly network**, not a social network — no feeds, likes or follows.

---

## 10. Search

- Phase 1: Filament's built-in per-resource search + filters (topic, region, status, type).
- Phase 2: a global full-text search over projects, publications, documents, data items
  and library entries via PostgreSQL `tsvector` (or Laravel Scout with the database driver).
  English-first, language-aware where possible.

---

## 11. AI assistance (assistive only)

The AI never makes scholarly decisions; it supports researchers. Planned actions, hooked
into the data model:
- Summarise interview transcripts; suggest qualitative codes; cluster themes.
- Compare literature; find references; language/style improvement.
- Draft executive summaries; consistency checks; document search.

It runs through the shared **LiteLLM gateway** (`app/Services/CoThinker.php`), addressing a
logical role alias — the provider/model is decided centrally, never in app code.

---

## 12. Future extensions

- Real-time collaborative editing (Yjs/WebSockets) on top of the versioned documents.
- Global full-text / semantic search (pgvector) and cited Q&A over the corpus.
- DOI minting (e.g. DataCite), ORCID linking.
- Content internationalisation (English default; further languages add-on).
- Evidence-collection UI linking coded excerpts to findings.
- S3-compatible object storage; fine-grained Laravel policies; export (PDF, citation files).
