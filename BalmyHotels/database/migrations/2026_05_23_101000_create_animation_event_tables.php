<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animation_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('branch_id', 'anim_events_branch_fk')->references('id')->on('branches')->cascadeOnDelete();
            $table->foreign('created_by', 'anim_events_creator_fk')->references('id')->on('users')->nullOnDelete();
            $table->index(['branch_id', 'is_active'], 'anim_events_branch_active_idx');
        });

        Schema::create('animation_event_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animation_event_id');
            $table->date('event_date');
            $table->timestamps();

            $table->foreign('animation_event_id', 'anim_dates_event_fk')->references('id')->on('animation_events')->cascadeOnDelete();
            $table->unique(['animation_event_id', 'event_date'], 'anim_dates_event_date_unique');
            $table->index('event_date', 'anim_dates_event_date_idx');
        });

        Schema::create('animation_event_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animation_event_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('animation_event_id', 'anim_participants_event_fk')->references('id')->on('animation_events')->cascadeOnDelete();
            $table->index('animation_event_id', 'anim_participants_event_idx');
        });

        Schema::create('animation_event_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('animation_event_date_id');
            $table->unsignedBigInteger('animation_event_participant_id');
            $table->unsignedBigInteger('checked_by')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->foreign('animation_event_date_id', 'anim_att_date_fk')->references('id')->on('animation_event_dates')->cascadeOnDelete();
            $table->foreign('animation_event_participant_id', 'anim_att_participant_fk')->references('id')->on('animation_event_participants')->cascadeOnDelete();
            $table->foreign('checked_by', 'anim_att_checked_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->unique(['animation_event_date_id', 'animation_event_participant_id'], 'anim_att_date_participant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animation_event_attendances');
        Schema::dropIfExists('animation_event_participants');
        Schema::dropIfExists('animation_event_dates');
        Schema::dropIfExists('animation_events');
    }
};
