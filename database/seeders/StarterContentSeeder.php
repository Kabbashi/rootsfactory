<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Publication;
use App\Models\Region;
use App\Models\ResearchProject;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter content so the platform doesn't open empty: research themes, help
 * FAQs, a sample research project, a published paper, a few library entries
 * and some member profiles.
 *
 * Idempotent — every row is matched on a natural key, so running it again
 * updates rather than duplicates.
 */
class StarterContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTopics();
        $this->seedFaqs();
        $this->seedMembers();
        $this->seedResearch();
        // Knowledge Library entries need real uploaded files (documents.path is
        // required), so they are added through the workspace, not seeded.
    }

    private function seedTopics(): void
    {
        $topics = [
            ['Climate adaptation finance', 'How communities pay for resilience.'],
            ['Food systems & smallholders', 'Productivity, markets and nutrition at the farm level.'],
            ['Locally led development', 'Shifting power and decisions to local actors.'],
            ['Water, sanitation & hygiene', 'Access, infrastructure and behaviour change.'],
            ['Gender & inclusion', 'Equity as a lens across every study.'],
        ];

        foreach ($topics as [$name, $description]) {
            Topic::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'description' => $description]);
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['Getting started', 'What is Roots Factory?', 'An independent international research network: a shared digital space for collaborative qualitative research, joint data analysis and open scientific writing.'],
            ['Getting started', 'How do I sign in?', 'Use the "Sign in with conceptnote" button. Your existing conceptnote account works here (single sign-on) — no second password.'],
            ['Research', 'What is the Collaborative Workspace?', 'Every research project gets its own space with documents, tasks, discussions, references and qualitative data — so the team can write and analyse together.'],
            ['Data', 'What kind of data does the Data Hub hold?', 'Qualitative material: interview transcripts, focus groups, field notes and observations, organised with codes, categories and themes. It is not a statistical big-data platform.'],
            ['Publishing', 'How does a paper get published?', 'It moves through the editorial workflow — draft, internal review, peer review, revision, copy-editing, approval, and finally publication — with versions tracked at every step.'],
            ['AI', 'Does the AI make research decisions?', 'No. AI only assists — summarising transcripts, suggesting codes, finding references, improving language. Researchers make every scholarly decision.'],
        ];

        // Drop FAQs left over from the earlier (CRM-flavoured) catalogue.
        Faq::whereIn('question', [
            'What is the difference between the Funding and Opportunity Centers?',
            'How does an idea become a publication?',
            'Who can publish?',
            'How does the Research Center answer questions?',
        ])->delete();

        $sort = 0;
        foreach ($faqs as [$category, $question, $answer]) {
            Faq::updateOrCreate(['question' => $question], [
                'answer' => $answer, 'category' => $category, 'sort' => $sort++, 'published' => true,
            ]);
        }
    }

    private function seedMembers(): void
    {
        // Give the earliest member an editor role and a fuller scholarly profile.
        $editor = User::query()->where('role', '!=', 'system')->orderBy('id')->first();

        if ($editor) {
            $editor->forceFill([
                'role' => $editor->role === 'admin' ? 'admin' : 'editor',
                'title' => $editor->title ?: 'Senior researcher & editor',
                'bio' => $editor->bio ?: 'Researcher in development cooperation with a focus on qualitative, locally led evaluation.',
                'expertise' => $editor->expertise ?: ['Qualitative methods', 'Evaluation', 'Policy research'],
                'country_experience' => $editor->country_experience ?: ['Kenya', 'Uganda', 'Bangladesh'],
                'languages' => $editor->languages ?: ['English', 'German', 'Swahili'],
                'method_competencies' => $editor->method_competencies ?: ['Interviews', 'Focus groups', 'Thematic analysis'],
            ])->save();
        }
    }

    private function seedResearch(): void
    {
        $lead = User::query()->where('role', '!=', 'system')->orderBy('id')->first();
        $climate = Topic::where('slug', 'climate-adaptation-finance')->first();
        $eastAfrica = Region::where('slug', 'east-africa')->first();

        $project = ResearchProject::updateOrCreate(
            ['slug' => 'climate-finance-local-actors'],
            [
                'title' => 'How climate adaptation finance reaches local actors',
                'kind' => 'field_study',
                'status' => 'active',
                'lead_user_id' => $lead?->id,
                'summary' => 'A multi-country field study on whether and how climate adaptation finance reaches community-level actors.',
                'objectives' => 'Understand the pathways, blockages and local experiences of adaptation finance.',
                'research_questions' => "1. How does adaptation finance flow to local actors?\n2. What blocks or enables that flow?",
                'methodology' => 'Qualitative: semi-structured interviews, focus groups and document review across two countries.',
                'data_collection' => 'Field interviews and focus groups with community organisations and intermediaries.',
            ],
        );

        if ($lead && ! $project->members()->whereKey($lead->id)->exists()) {
            $project->members()->attach($lead->id, ['role' => 'lead']);
        }
        if ($climate) {
            $project->topics()->syncWithoutDetaching([$climate->id]);
        }
        if ($eastAfrica) {
            $project->regions()->syncWithoutDetaching([$eastAfrica->id]);
        }

        $publication = Publication::updateOrCreate(
            ['slug' => 'reaching-the-last-mile-of-adaptation-finance'],
            [
                'research_project_id' => $project->id,
                'title' => 'Reaching the last mile of adaptation finance',
                'type' => 'working_paper',
                'status' => 'published',
                'abstract' => 'Drawing on field interviews, this paper examines where adaptation finance reaches local actors — and where it stalls.',
                'body' => "## Introduction\n\nThis working paper presents early findings from our field study on adaptation finance.\n\n## Findings\n\nLocal actors report long, opaque funding chains and limited say in priorities.",
                'citation' => 'Roots Factory (2026). Reaching the last mile of adaptation finance. Working paper.',
                'published_at' => now(),
            ],
        );

        if ($lead && ! $publication->authors()->whereKey($lead->id)->exists()) {
            $publication->authors()->attach($lead->id, ['role' => 'author', 'order' => 0]);
        }

        if ($publication->versions()->count() === 0) {
            $version = $publication->versions()->create([
                'created_by' => $lead?->id,
                'version_no' => 1,
                'abstract' => $publication->abstract,
                'body' => $publication->body,
                'changelog' => 'Initial version',
            ]);
            $publication->forceFill(['current_version_id' => $version->id])->saveQuietly();
        }
    }
}
