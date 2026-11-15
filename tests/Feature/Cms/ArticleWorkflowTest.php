<?php

namespace Tests\Feature\Cms;

use App\Enums\ArticleStatus;
use App\Livewire\Cms\Articles\Form;
use App\Livewire\Cms\Articles\Index;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function articleFor(User $user, ArticleStatus $status = ArticleStatus::Draft): Article
    {
        $article = Article::factory()->create([
            'user_id' => $user->id,
            'status' => $status,
            'published_at' => $status === ArticleStatus::Published ? now()->subDay() : null,
        ]);
        $article->categories()->attach(Category::factory()->create());

        return $article;
    }

    public function test_writer_can_publish_own_article(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer);
        $this->actingAs($writer);

        Livewire::test(Index::class)
            ->call('publish', $article->id)
            ->assertDispatched('toast');

        $article->refresh();
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->published_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'article.published', 'subject_id' => $article->id]);
        $this->get(route('articles.show', $article))->assertOk();
    }

    public function test_writer_can_submit_own_article_for_review(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer);
        $this->actingAs($writer);

        Livewire::test(Index::class)->call('submitReview', $article->id);

        $this->assertSame(ArticleStatus::InReview, $article->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'article.submitted_review', 'subject_id' => $article->id]);
    }

    public function test_writer_cannot_schedule_article(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer);
        $this->actingAs($writer);

        $this->assertFalse($writer->can('schedule', $article));
        Livewire::test(Index::class)->call('openSchedule', $article->id)->assertStatus(403);
        Livewire::test(Form::class, ['article' => $article])->call('schedule')->assertStatus(403);
        $this->assertSame(ArticleStatus::Draft, $article->refresh()->status);
    }

    public function test_admin_can_schedule_article_and_it_publishes_when_due(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin);
        $this->actingAs($admin);

        $at = now()->addHours(3)->format('Y-m-d\TH:i');

        Livewire::test(Index::class)
            ->call('openSchedule', $article->id)
            ->assertSet('scheduleId', $article->id)
            ->set('scheduleAt', $at)
            ->call('scheduleConfirmed')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $article->refresh();
        $this->assertSame(ArticleStatus::Scheduled, $article->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'article.scheduled', 'subject_id' => $article->id]);

        // Belum waktunya: tidak tampil publik.
        $this->get(route('articles.show', $article))->assertNotFound();

        // Setelah waktu tayang tiba: tampil walau status masih Scheduled (cron belum jalan).
        $this->travel(4)->hours();
        $this->get(route('articles.show', $article))->assertOk();

        // Command scheduler menormalkan status menjadi Published.
        $this->artisan('articles:publish-scheduled')->assertSuccessful();
        $this->assertSame(ArticleStatus::Published, $article->refresh()->status);
    }

    public function test_schedule_rejects_past_datetime(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin);
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('openSchedule', $article->id)
            ->set('scheduleAt', now()->subHour()->format('Y-m-d\TH:i'))
            ->call('scheduleConfirmed')
            ->assertHasErrors(['scheduleAt']);

        $this->assertSame(ArticleStatus::Draft, $article->refresh()->status);
    }

    public function test_admin_can_request_revision_with_note(): void
    {
        $admin = User::factory()->admin()->create();
        $writer = User::factory()->create();
        $article = $this->articleFor($writer, ArticleStatus::InReview);
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('openRevision', $article->id)
            ->set('revisionNote', 'Mohon perbaiki paragraf pembuka dan tambahkan sumber.')
            ->call('revisionConfirmed')
            ->assertHasNoErrors();

        $article->refresh();
        $this->assertSame(ArticleStatus::Revision, $article->status);
        $this->assertSame('Mohon perbaiki paragraf pembuka dan tambahkan sumber.', $article->revision_note);
        $this->assertDatabaseHas('activity_logs', ['action' => 'article.revision_requested', 'subject_id' => $article->id]);

        // Writer melihat catatan revisi pada daftar beritanya.
        $this->actingAs($writer);
        Livewire::test(Index::class)->assertSee('Mohon perbaiki paragraf pembuka');
    }

    public function test_revision_requires_note(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin, ArticleStatus::InReview);
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('openRevision', $article->id)
            ->set('revisionNote', '')
            ->call('revisionConfirmed')
            ->assertHasErrors(['revisionNote']);

        $this->assertSame(ArticleStatus::InReview, $article->refresh()->status);
    }

    public function test_writer_cannot_request_revision(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer);
        $this->actingAs($writer);

        Livewire::test(Index::class)->call('openRevision', $article->id)->assertStatus(403);
    }

    public function test_schedule_input_is_interpreted_as_jakarta_time(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin);
        $this->actingAs($admin);

        $local = now('Asia/Jakarta')->addDay()->setTime(10, 0);

        Livewire::test(Index::class)
            ->call('openSchedule', $article->id)
            ->set('scheduleAt', $local->format('Y-m-d\TH:i'))
            ->call('scheduleConfirmed')
            ->assertHasNoErrors();

        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('03:00', $article->refresh()->published_at->utc()->format('H:i'));
    }

    public function test_writer_cannot_unpublish_by_submitting_published_article_for_review(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer, ArticleStatus::Published);
        $this->actingAs($writer);

        Livewire::test(Index::class)->call('submitReview', $article->id)->assertStatus(403);
        Livewire::test(Form::class, ['article' => $article])->assertStatus(403);

        $this->assertSame(ArticleStatus::Published, $article->refresh()->status);
    }

    public function test_writer_cannot_publish_before_admin_schedule(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer, ArticleStatus::Scheduled);
        $article->update(['published_at' => $at = now()->addWeek()->startOfMinute()]);
        $this->actingAs($writer);

        Livewire::test(Index::class)->call('publish', $article->id)->assertStatus(403);
        Livewire::test(Form::class, ['article' => $article])->assertStatus(403);

        $article->refresh();
        $this->assertSame(ArticleStatus::Scheduled, $article->status);
        $this->assertTrue($article->published_at->eq($at));
    }

    public function test_admin_can_publish_scheduled_article_early(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor(User::factory()->create(), ArticleStatus::Scheduled);
        $article->update(['published_at' => now()->addWeek()]);
        $this->actingAs($admin);

        Livewire::test(Index::class)->call('publish', $article->id)->assertDispatched('toast');

        $article->refresh();
        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertTrue($article->published_at->lte(now()));
    }

    public function test_admin_cannot_make_invalid_status_transitions(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $published = $this->articleFor($admin, ArticleStatus::Published);
        Livewire::test(Index::class)->call('restore', $published->id)->assertStatus(403);
        Livewire::test(Index::class)->call('openSchedule', $published->id)->assertStatus(403);
        Livewire::test(Index::class)->call('openRevision', $published->id)->assertStatus(403);
        $this->assertSame(ArticleStatus::Published, $published->refresh()->status);

        $draft = $this->articleFor($admin);
        Livewire::test(Index::class)->call('archive', $draft->id)->assertStatus(403);
        $this->assertSame(ArticleStatus::Draft, $draft->refresh()->status);
    }

    public function test_writer_menu_hides_publish_for_scheduled_article(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer, ArticleStatus::Scheduled);
        $article->update(['published_at' => now()->addWeek()]);
        $this->actingAs($writer);

        Livewire::test(Index::class)->assertDontSeeHtml('wire:click="publish('.$article->id.')"');
    }

    public function test_writer_can_only_edit_own_draft_or_revision_articles(): void
    {
        $writer = User::factory()->create();
        $this->actingAs($writer);

        foreach ([ArticleStatus::Draft, ArticleStatus::Revision] as $status) {
            $article = $this->articleFor($writer, $status);
            $this->get(route('cms.articles.edit', $article))->assertOk();
            Livewire::test(Form::class, ['article' => $article])->set('title', 'Judul Revisi '.$status->value)->call('save')->assertHasNoErrors();
            $this->assertSame('Judul Revisi '.$status->value, $article->refresh()->title);
        }

        foreach ([ArticleStatus::InReview, ArticleStatus::Scheduled, ArticleStatus::Published, ArticleStatus::Archived] as $status) {
            $article = $this->articleFor($writer, $status);
            $this->assertFalse($writer->can('update', $article), $status->value);
            $this->get(route('cms.articles.edit', $article))->assertForbidden();
            Livewire::test(Index::class)->assertDontSeeHtml('href="'.route('cms.articles.edit', $article).'"');
        }

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('cms.articles.edit', $this->articleFor($writer, ArticleStatus::Published)))->assertOk();
    }

    public function test_unauthorized_form_action_does_not_save_changes(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer);
        $this->actingAs($writer);

        Livewire::test(Form::class, ['article' => $article])
            ->set('title', 'Judul yang tidak boleh tersimpan')
            ->call('schedule')
            ->assertStatus(403);

        $this->assertNotSame('Judul yang tidak boleh tersimpan', $article->refresh()->title);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'article.updated', 'subject_id' => $article->id]);
    }

    public function test_invalid_schedule_time_does_not_save_changes(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin);
        $this->actingAs($admin);

        Livewire::test(Form::class, ['article' => $article])
            ->set('title', 'Judul yang tidak boleh tersimpan')
            ->set('scheduledAt', now()->subHour()->format('Y-m-d\TH:i'))
            ->call('schedule')
            ->assertHasErrors(['scheduledAt']);

        $this->assertNotSame('Judul yang tidak boleh tersimpan', $article->refresh()->title);
        $this->assertSame(ArticleStatus::Draft, $article->status);
    }

    public function test_admin_can_archive_and_restore_article(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin, ArticleStatus::Published);
        $this->actingAs($admin);

        Livewire::test(Index::class)->call('archive', $article->id);
        $this->assertSame(ArticleStatus::Archived, $article->refresh()->status);
        $this->get(route('articles.show', $article))->assertNotFound();

        Livewire::test(Index::class)->call('restore', $article->id);
        $this->assertSame(ArticleStatus::Draft, $article->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'article.archived', 'subject_id' => $article->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'article.restored', 'subject_id' => $article->id]);
    }

    public function test_archived_article_cannot_be_published_directly(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin, ArticleStatus::Archived);
        $this->actingAs($admin);

        Livewire::test(Index::class)
            ->call('publish', $article->id)
            ->assertDispatched('toast', type: 'error');

        $this->assertSame(ArticleStatus::Archived, $article->refresh()->status);
    }

    public function test_writer_cannot_archive_or_restore(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer, ArticleStatus::Published);
        $this->actingAs($writer);

        Livewire::test(Index::class)->call('archive', $article->id)->assertStatus(403);
        Livewire::test(Index::class)->call('restore', $article->id)->assertStatus(403);
    }

    public function test_republishing_keeps_original_published_at(): void
    {
        $admin = User::factory()->admin()->create();
        $article = $this->articleFor($admin, ArticleStatus::Published);
        $original = $article->published_at->toDateTimeString();
        $this->actingAs($admin);

        $this->travel(2)->days();
        Livewire::test(Index::class)->call('publish', $article->id);

        $this->assertSame($original, $article->refresh()->published_at->toDateTimeString());
    }

    public function test_form_publish_action_publishes_and_redirects(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer);
        $this->actingAs($writer);

        Livewire::test(Form::class, ['article' => $article])
            ->call('publish')
            ->assertHasNoErrors()
            ->assertRedirect(route('cms.articles'));

        $this->assertSame(ArticleStatus::Published, $article->refresh()->status);
    }

    public function test_form_submit_review_action_sets_in_review(): void
    {
        $writer = User::factory()->create();
        $article = $this->articleFor($writer, ArticleStatus::Revision);
        $article->update(['revision_note' => 'Catatan lama']);
        $this->actingAs($writer);

        Livewire::test(Form::class, ['article' => $article])
            ->call('submitReview')
            ->assertRedirect(route('cms.articles'));

        $article->refresh();
        $this->assertSame(ArticleStatus::InReview, $article->status);
        $this->assertNull($article->revision_note);
    }

    public function test_scheduled_article_in_future_is_hidden_from_public_list(): void
    {
        Article::factory()->scheduled()->create();
        Article::factory()->published()->create();

        $this->assertSame(1, Article::published()->count());
    }
}