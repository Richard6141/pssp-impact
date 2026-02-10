# 🚀 PS SP IMPACT+ v2.0 - Architecture Complète (Fin)

## 7️⃣ Gestion des Utilisateurs

### Base de données

```php
// Migration: create_user_invitations_table
Schema::create('user_invitations', function (Blueprint $table) {
    $table->uuid('invitation_id')->primary();
    $table->string('email');
    $table->string('firstname');
    $table->string('lastname');
    $table->string('role_name');
    $table->uuid('site_id')->nullable();
    $table->string('token', 64)->unique();
    $table->uuid('invited_by');
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->uuid('created_user_id')->nullable();
    $table->enum('status', ['pending', 'accepted', 'expired', 'cancelled'])->default('pending');
    $table->timestamps();
    
    $table->foreign('invited_by')->references('user_id')->on('users')->onDelete('cascade');
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('set null');
    $table->foreign('created_user_id')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['email', 'status']);
});

// Migration: create_user_profiles_table
Schema::create('user_profiles', function (Blueprint $table) {
    $table->uuid('profile_id')->primary();
    $table->uuid('user_id')->unique();
    $table->string('job_function')->nullable(); // Métier
    $table->string('zone')->nullable(); // Zone géographique d'intervention
    $table->json('availability')->nullable(); // Disponibilités (jours/heures)
    $table->json('skills')->nullable(); // Compétences
    $table->json('certifications')->nullable(); // Certifications
    $table->string('emergency_contact_name')->nullable();
    $table->string('emergency_contact_phone')->nullable();
    $table->date('hire_date')->nullable();
    $table->decimal('hourly_rate', 8, 2)->nullable();
    $table->json('documents')->nullable(); // CV, contrat, etc.
    $table->timestamps();
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
});

// Migration: create_user_site_assignments_table (multi-site)
Schema::create('user_site_assignments', function (Blueprint $table) {
    $table->id();
    $table->uuid('user_id');
    $table->uuid('site_id');
    $table->boolean('is_primary')->default(false);
    $table->date('assigned_from')->nullable();
    $table->date('assigned_to')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->unique(['user_id', 'site_id']);
});

// Migration: create_user_activity_logs_table
Schema::create('user_activity_logs', function (Blueprint $table) {
    $table->uuid('activity_id')->primary();
    $table->uuid('user_id');
    $table->string('activity_type'); // login, logout, view, create, update, delete
    $table->string('entity_type')->nullable();
    $table->string('entity_id')->nullable();
    $table->json('metadata')->nullable();
    $table->string('ip_address', 45);
    $table->timestamp('performed_at');
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    $table->index(['user_id', 'performed_at']);
    $table->index(['activity_type', 'performed_at']);
});
```

### Services

```php
// app/Services/UserInvitationService.php
class UserInvitationService
{
    public function invite(array $data): UserInvitation
    {
        $invitation = UserInvitation::create([
            'invitation_id' => Str::uuid(),
            'email' => $data['email'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'role_name' => $data['role_name'],
            'site_id' => $data['site_id'] ?? null,
            'token' => Str::random(64),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);
        
        // Envoyer l'email d'invitation
        Mail::to($invitation->email)->send(new UserInvitationMail($invitation));
        
        return $invitation;
    }
    
    public function accept(string $token, array $data): User
    {
        $invitation = UserInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();
        
        // Créer l'utilisateur
        $user = User::create([
            'user_id' => Str::uuid(),
            'firstname' => $invitation->firstname,
            'lastname' => $invitation->lastname,
            'username' => $data['username'],
            'email' => $invitation->email,
            'password' => Hash::make($data['password']),
            'site_id' => $invitation->site_id,
            'isActive' => true,
        ]);
        
        // Assigner le rôle
        $user->assignRole($invitation->role_name);
        
        // Créer le profil
        UserProfile::create([
            'profile_id' => Str::uuid(),
            'user_id' => $user->user_id,
        ]);
        
        // Marquer l'invitation comme acceptée
        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'created_user_id' => $user->user_id,
        ]);
        
        return $user;
    }
    
    public function resend(string $invitationId): void
    {
        $invitation = UserInvitation::findOrFail($invitationId);
        
        if ($invitation->status !== 'pending') {
            throw new \Exception('Cette invitation ne peut être renvoyée');
        }
        
        $invitation->update([
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);
        
        Mail::to($invitation->email)->send(new UserInvitationMail($invitation));
    }
}

// app/Services/UserImportService.php
class UserImportService
{
    public function import(UploadedFile $file): array
    {
        $data = Excel::toCollection(new UsersImport, $file)->first();
        
        $imported = [];
        $errors = [];
        
        foreach ($data as $index => $row) {
            try {
                $user = $this->createUserFromRow($row);
                $imported[] = $user;
            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ];
            }
        }
        
        return [
            'imported' => $imported,
            'errors' => $errors,
            'total' => $data->count(),
            'success_count' => count($imported),
            'error_count' => count($errors),
        ];
    }
    
    private function createUserFromRow($row): User
    {
        $user = User::create([
            'user_id' => Str::uuid(),
            'firstname' => $row['prenom'],
            'lastname' => $row['nom'],
            'username' => $row['username'] ?? Str::slug($row['prenom'] . '.' . $row['nom']),
            'email' => $row['email'],
            'password' => Hash::make($row['password'] ?? 'password123'),
            'phone' => $row['telephone'] ?? null,
            'isActive' => true,
        ]);
        
        // Assigner le rôle
        if (isset($row['role'])) {
            $user->assignRole($row['role']);
        }
        
        // Assigner au site
        if (isset($row['site'])) {
            $site = Site::where('nom', $row['site'])->first();
            if ($site) {
                $user->update(['site_id' => $site->site_id]);
            }
        }
        
        return $user;
    }
    
    public function exportTemplate(): string
    {
        $headers = [
            'prenom',
            'nom',
            'email',
            'username',
            'telephone',
            'role',
            'site',
        ];
        
        $csv = [];
        $csv[] = implode(';', $headers);
        $csv[] = implode(';', [
            'Jean',
            'Dupont',
            'jean.dupont@example.com',
            'j.dupont',
            '221771234567',
            'Agent',
            'Site Principal',
        ]);
        
        $filename = storage_path('app/templates/import_users_template.csv');
        file_put_contents($filename, implode("\n", $csv));
        
        return $filename;
    }
}
```

---

## 8️⃣ Notifications & Communication

### Base de données

```php
// Migration: create_notification_channels_table
Schema::create('notification_channels', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // email, whatsapp, sms
    $table->boolean('is_active')->default(true);
    $table->json('config'); // API keys, URLs, etc.
    $table->timestamps();
    
    $table->unique('name');
});

// Migration: create_notification_templates_table
Schema::create('notification_templates', function (Blueprint $table) {
    $table->uuid('template_id')->primary();
    $table->string('code')->unique(); // facture_created, collecte_assigned, etc.
    $table->string('name');
    $table->enum('event', ['facture_created', 'facture_validated', 'payment_received', 'collecte_assigned', 'incident_reported', 'user_invited']);
    $table->json('channels'); // ['email', 'whatsapp', 'sms']
    $table->text('email_subject')->nullable();
    $table->text('email_body')->nullable();
    $table->text('sms_body')->nullable();
    $table->text('whatsapp_body')->nullable();
    $table->json('variables')->nullable(); // Variables disponibles: {user_name}, {facture_number}, etc.
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Migration: create_notification_queue_table
Schema::create('notification_queue', function (Blueprint $table) {
    $table->uuid('queue_id')->primary();
    $table->uuid('template_id');
    $table->uuid('user_id')->nullable();
    $table->string('recipient'); // Email ou téléphone
    $table->enum('channel', ['email', 'whatsapp', 'sms']);
    $table->json('data'); // Données pour remplacer les variables
    $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
    $table->integer('attempts')->default(0);
    $table->timestamp('scheduled_for')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->text('error_message')->nullable();
    $table->json('response_data')->nullable();
    $table->timestamps();
    
    $table->foreign('template_id')->references('template_id')->on('notification_templates')->onDelete('cascade');
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['status', 'scheduled_for']);
});

// Migration: create_notification_center_table
Schema::create('notification_center', function (Blueprint $table) {
    $table->uuid('notification_id')->primary();
    $table->uuid('user_id');
    $table->string('type'); // info, warning, success, error
    $table->string('title');
    $table->text('message');
    $table->string('action_url')->nullable();
    $table->string('action_label')->nullable();
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    $table->index(['user_id', 'is_read']);
});

// Migration: create_whatsapp_messages_table
Schema::create('whatsapp_messages', function (Blueprint $table) {
    $table->uuid('message_id')->primary();
    $table->string('to_number');
    $table->string('from_number')->nullable();
    $table->text('message');
    $table->string('media_url')->nullable();
    $table->string('whatsapp_message_id')->nullable();
    $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed']);
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['to_number', 'status']);
});
```

### Services

```php
// app/Services/NotificationService.php
class NotificationService
{
    public function send(string $templateCode, $recipient, array $data): void
    {
        $template = NotificationTemplate::where('code', $templateCode)
            ->where('is_active', true)
            ->firstOrFail();
        
        foreach ($template->channels as $channel) {
            $this->queueNotification($template, $recipient, $channel, $data);
        }
    }
    
    private function queueNotification($template, $recipient, string $channel, array $data): void
    {
        $recipientValue = match($channel) {
            'email' => is_string($recipient) ? $recipient : $recipient->email,
            'sms', 'whatsapp' => is_string($recipient) ? $recipient : $recipient->phone,
        };
        
        NotificationQueue::create([
            'queue_id' => Str::uuid(),
            'template_id' => $template->template_id,
            'user_id' => is_object($recipient) ? $recipient->user_id : null,
            'recipient' => $recipientValue,
            'channel' => $channel,
            'data' => $data,
            'scheduled_for' => now(),
        ]);
    }
    
    public function process(): void
    {
        $notifications = NotificationQueue::where('status', 'pending')
            ->where('scheduled_for', '<=', now())
            ->where('attempts', '<', 3)
            ->limit(50)
            ->get();
        
        foreach ($notifications as $notification) {
            try {
                $notification->increment('attempts');
                
                match($notification->channel) {
                    'email' => $this->sendEmail($notification),
                    'whatsapp' => $this->sendWhatsApp($notification),
                    'sms' => $this->sendSMS($notification),
                };
                
                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                
            } catch (\Exception $e) {
                $notification->update([
                    'status' => $notification->attempts >= 3 ? 'failed' : 'pending',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }
    
    private function sendEmail(NotificationQueue $notification): void
    {
        $template = $notification->template;
        $subject = $this->replaceVariables($template->email_subject, $notification->data);
        $body = $this->replaceVariables($template->email_body, $notification->data);
        
        Mail::to($notification->recipient)->send(new GenericNotificationMail($subject, $body));
    }
    
    private function sendWhatsApp(NotificationQueue $notification): void
    {
        $config = NotificationChannel::where('name', 'whatsapp')->firstOrFail();
        $template = $notification->template;
        $message = $this->replaceVariables($template->whatsapp_body, $notification->data);
        
        // Utiliser Twilio WhatsApp API
        $client = new \Twilio\Rest\Client(
            $config->config['account_sid'],
            $config->config['auth_token']
        );
        
        $response = $client->messages->create(
            'whatsapp:' . $notification->recipient,
            [
                'from' => 'whatsapp:' . $config->config['from_number'],
                'body' => $message,
            ]
        );
        
        WhatsAppMessage::create([
            'message_id' => Str::uuid(),
            'to_number' => $notification->recipient,
            'from_number' => $config->config['from_number'],
            'message' => $message,
            'whatsapp_message_id' => $response->sid,
            'status' => 'sent',
        ]);
        
        $notification->update(['response_data' => ['sid' => $response->sid]]);
    }
    
    private function sendSMS(NotificationQueue $notification): void
    {
        $config = NotificationChannel::where('name', 'sms')->firstOrFail();
        $template = $notification->template;
        $message = $this->replaceVariables($template->sms_body, $notification->data);
        
        // Utiliser un provider SMS (ex: Twilio, Vonage)
        $client = new \Twilio\Rest\Client(
            $config->config['account_sid'],
            $config->config['auth_token']
        );
        
        $response = $client->messages->create(
            $notification->recipient,
            [
                'from' => $config->config['from_number'],
                'body' => $message,
            ]
        );
        
        $notification->update(['response_data' => ['sid' => $response->sid]]);
    }
    
    private function replaceVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
}

// app/Services/NotificationCenterService.php
class NotificationCenterService
{
    public function create(User $user, array $data): void
    {
        NotificationCenter::create([
            'notification_id' => Str::uuid(),
            'user_id' => $user->user_id,
            'type' => $data['type'] ?? 'info',
            'title' => $data['title'],
            'message' => $data['message'],
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? null,
        ]);
        
        // Broadcast en temps réel avec Laravel Echo
        broadcast(new NewNotification($user, $data));
    }
    
    public function getUnread(User $user): Collection
    {
        return NotificationCenter::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function markAsRead(string $notificationId): void
    {
        NotificationCenter::findOrFail($notificationId)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
    
    public function markAllAsRead(User $user): void
    {
        NotificationCenter::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
```

---

## 9️⃣ Documents & Archivage

### Base de données

```php
// Migration: create_documents_table
Schema::create('documents', function (Blueprint $table) {
    $table->uuid('document_id')->primary();
    $table->string('name');
    $table->string('original_filename');
    $table->string('path');
    $table->string('mime_type');
    $table->unsignedBigInteger('size'); // bytes
    $table->enum('category', ['facture', 'contract', 'proof', 'signature', 'rapport', 'other']);
    $table->uuid('uploaded_by');
    $table->uuid('related_to_id')->nullable(); // ID de l'entité liée
    $table->string('related_to_type')->nullable(); // Type de l'entité (Facture, Site, etc.)
    $table->text('description')->nullable();
    $table->json('metadata')->nullable();
    $table->integer('version')->default(1);
    $table->uuid('previous_version_id')->nullable();
    $table->boolean('is_archived')->default(false);
    $table->timestamp('archived_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->foreign('uploaded_by')->references('user_id')->on('users')->onDelete('cascade');
    $table->foreign('previous_version_id')->references('document_id')->on('documents')->onDelete('set null');
    $table->index(['category', 'created_at']);
    $table->index(['related_to_type', 'related_to_id']);
});

// Migration: create_document_versions_table
Schema::create('document_versions', function (Blueprint $table) {
    $table->uuid('version_id')->primary();
    $table->uuid('document_id');
    $table->integer('version_number');
    $table->string('path');
    $table->unsignedBigInteger('size');
    $table->uuid('updated_by');
    $table->text('change_note')->nullable();
    $table->timestamps();
    
    $table->foreign('document_id')->references('document_id')->on('documents')->onDelete('cascade');
    $table->foreign('updated_by')->references('user_id')->on('users')->onDelete('cascade');
    $table->unique(['document_id', 'version_number']);
});

// Migration: create_archive_periods_table
Schema::create('archive_periods', function (Blueprint $table) {
    $table->uuid('period_id')->primary();
    $table->string('name'); // Archives 2025
    $table->date('start_date');
    $table->date('end_date');
    $table->enum('status', ['active', 'closed', 'archived'])->default('active');
    $table->integer('document_count')->default(0);
    $table->unsignedBigInteger('total_size')->default(0);
    $table->uuid('closed_by')->nullable();
    $table->timestamp('closed_at')->nullable();
    $table->json('archive_index')->nullable(); // Index des documents archivés
    $table->timestamps();
    
    $table->foreign('closed_by')->references('user_id')->on('users')->onDelete('set null');
});

// Migration: create_document_signatures_table
Schema::create('document_signatures', function (Blueprint $table) {
    $table->uuid('signature_id')->primary();
    $table->uuid('document_id');
    $table->uuid('user_id');
    $table->string('signature_path');
    $table->string('ip_address', 45);
    $table->timestamp('signed_at');
    $table->string('hash'); // Hash du document au moment de la signature
    $table->timestamps();
    
    $table->foreign('document_id')->references('document_id')->on('documents')->onDelete('cascade');
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
    $table->unique(['document_id', 'user_id']);
});
```

### Services

```php
// app/Services/DocumentService.php
class DocumentService
{
    public function upload(UploadedFile $file, array $data): Document
    {
        $path = $file->store('documents/' . $data['category'], 'local');
        
        $document = Document::create([
            'document_id' => Str::uuid(),
            'name' => $data['name'] ?? $file->getClientOriginalName(),
            'original_filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'category' => $data['category'],
            'uploaded_by' => auth()->id(),
            'related_to_id' => $data['related_to_id'] ?? null,
            'related_to_type' => $data['related_to_type'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
        
        event(new DocumentUploaded($document));
        
        return $document;
    }
    
    public function createVersion(Document $document, UploadedFile $file, string $changeNote = null): DocumentVersion
    {
        $path = $file->store('documents/versions', 'local');
        
        $version = DocumentVersion::create([
            'version_id' => Str::uuid(),
            'document_id' => $document->document_id,
            'version_number' => $document->version + 1,
            'path' => $path,
            'size' => $file->getSize(),
            'updated_by' => auth()->id(),
            'change_note' => $changeNote,
        ]);
        
        // Mettre à jour le document principal
        $document->update([
            'path' => $path,
            'size' => $file->getSize(),
            'version' => $version->version_number,
            'previous_version_id' => $document->document_id,
        ]);
        
        return $version;
    }
    
    public function archive(Document $document, string $periodId): void
    {
        $document->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);
        
        $period = ArchivePeriod::findOrFail($periodId);
        $period->increment('document_count');
        $period->increment('total_size', $document->size);
        
        // Ajouter au l'index
        $index = $period->archive_index ?? [];
        $index[] = [
            'document_id' => $document->document_id,
            'name' => $document->name,
            'category' => $document->category,
            'archived_at' => now()->toDateTimeString(),
        ];
        
        $period->update(['archive_index' => $index]);
    }
    
    public function sign(Document $document, string $signaturePath): DocumentSignature
    {
        // Calculer le hash du document
        $hash = hash_file('sha256', storage_path('app/' . $document->path));
        
        return DocumentSignature::create([
            'signature_id' => Str::uuid(),
            'document_id' => $document->document_id,
            'user_id' => auth()->id(),
            'signature_path' => $signaturePath,
            'ip_address' => request()->ip(),
            'signed_at' => now(),
            'hash' => $hash,
        ]);
    }
    
    public function verifyIntegrity(Document $document): bool
    {
        $signature = $document->signatures()->latest()->first();
        
        if (!$signature) {
            return false; // Pas de signature
        }
        
        $currentHash = hash_file('sha256', storage_path('app/' . $document->path));
        
        return $currentHash === $signature->hash;
    }
}
```

---

## 🔟 Qualité & Support

### Base de données

```php
// Migration: create_reclamations_table
Schema::create('reclamations', function (Blueprint $table) {
    $table->uuid('reclamation_id')->primary();
    $table->string('ticket_number')->unique();
    $table->uuid('site_id')->nullable();
    $table->uuid('created_by');
    $table->enum('type', ['service', 'facturation', 'qualite', 'autre']);
    $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
    $table->string('subject');
    $table->text('description');
    $table->enum('status', ['nouveau', 'en_cours', 'resolu', 'clos', 'rejete'])->default('nouveau');
    $table->uuid('assigned_to')->nullable();
    $table->timestamp('assigned_at')->nullable();
    $table->timestamp('resolved_at')->nullable();
    $table->text('resolution')->nullable();
    $table->integer('satisfaction_score')->nullable(); // 1-5
    $table->text('satisfaction_comment')->nullable();
    $table->timestamps();
    
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('set null');
    $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
    $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('set null');
    $table->index(['status', 'priority']);
});

// Migration: create_reclamation_responses_table
Schema::create('reclamation_responses', function (Blueprint $table) {
    $table->uuid('response_id')->primary();
    $table->uuid('reclamation_id');
    $table->uuid('user_id');
    $table->text('message');
    $table->boolean('is_internal')->default(false);
    $table->json('attachments')->nullable();
    $table->timestamps();
    
    $table->foreign('reclamation_id')->references('reclamation_id')->on('reclamations')->onDelete('cascade');
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
});

// Migration: create_site_quality_scores_table
Schema::create('site_quality_scores', function (Blueprint $table) {
    $table->uuid('score_id')->primary();
    $table->uuid('site_id');
    $table->date('period_date');
    $table->enum('period_type', ['month', 'quarter', 'year']);
    $table->decimal('ponctualite_score', 5, 2)->default(0); // %
    $table->decimal('qualite_service_score', 5, 2)->default(0);
    $table->decimal('satisfaction_client_score', 5, 2)->default(0);
    $table->decimal('conformite_sla_score', 5, 2)->default(0);
    $table->decimal('total_score', 5, 2)->default(0);
    $table->enum('grade', ['A', 'B', 'C', 'D', 'F'])->nullable();
    $table->timestamps();
    
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->unique(['site_id', 'period_date', 'period_type']);
});

// Migration: create_satisfaction_surveys_table
Schema::create('satisfaction_surveys', function (Blueprint $table) {
    $table->uuid('survey_id')->primary();
    $table->string('title');
    $table->text('description')->nullable();
    $table->uuid('site_id')->nullable();
    $table->json('questions'); // Questions du sondage
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_anonymous')->default(false);
    $table->uuid('created_by');
    $table->timestamps();
    
    $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
    $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
});

// Migration: create_survey_responses_table
Schema::create('survey_responses', function (Blueprint $table) {
    $table->uuid('response_id')->primary();
    $table->uuid('survey_id');
    $table->uuid('user_id')->nullable(); // Null si anonyme
    $table->json('answers'); // Réponses aux questions
    $table->text('comments')->nullable();
    $table->string('ip_address', 45);
    $table->timestamps();
    
    $table->foreign('survey_id')->references('survey_id')->on('satisfaction_surveys')->onDelete('cascade');
    $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
});
```

### Services

```php
// app/Services/ReclamationService.php
class ReclamationService
{
    public function create(array $data): Reclamation
    {
        $reclamation = Reclamation::create([
            'reclamation_id' => Str::uuid(),
            'ticket_number' => $this->generateTicketNumber(),
            'site_id' => $data['site_id'] ?? null,
            'created_by' => auth()->id(),
            'type' => $data['type'],
            'priority' => $data['priority'] ?? 'normal',
            'subject' => $data['subject'],
            'description' => $data['description'],
        ]);
        
        // Auto-assigner selon le type
        $this->autoAssign($reclamation);
        
        // Notifier
        event(new ReclamationCreated($reclamation));
        
        return $reclamation;
    }
    
    private function generateTicketNumber(): string
    {
        $count = Reclamation::whereDate('created_at', today())->count() + 1;
        return 'REC-' . now()->format('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    private function autoAssign(Reclamation $reclamation): void
    {
        // Logique d'assignation selon le type
        $role = match($reclamation->type) {
            'facturation' => 'Comptable',
            'service' => 'Responsable Operations',
            default => 'Admin',
        };
        
        $agent = User::role($role)->where('isActive', true)->first();
        
        if ($agent) {
            $reclamation->update([
                'assigned_to' => $agent->user_id,
                'assigned_at' => now(),
                'status' => 'en_cours',
            ]);
        }
    }
    
    public function addResponse(Reclamation $reclamation, array $data): ReclamationResponse
    {
        $response = ReclamationResponse::create([
            'response_id' => Str::uuid(),
            'reclamation_id' => $reclamation->reclamation_id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
            'is_internal' => $data['is_internal'] ?? false,
            'attachments' => $data['attachments'] ?? null,
        ]);
        
        // Notifier le créateur si ce n'est pas lui qui répond
        if (auth()->id() !== $reclamation->created_by && !$response->is_internal) {
            event(new ReclamationResponseAdded($reclamation, $response));
        }
        
        return $response;
    }
    
    public function resolve(Reclamation $reclamation, string $resolution): void
    {
        $reclamation->update([
            'status' => 'resolu',
            'resolved_at' => now(),
            'resolution' => $resolution,
        ]);
        
        // Envoyer enquête de satisfaction
        $this->sendSatisfactionSurvey($reclamation);
    }
    
    private function sendSatisfactionSurvey(Reclamation $reclamation): void
    {
        $creator = $reclamation->creator;
        
        Mail::to($creator->email)->send(new ReclamationSatisfactionSurvey($reclamation));
    }
}

// app/Services/QualityScoreService.php
class QualityScoreService
{
    public function calculateMonthlyScore(string $siteId, Carbon $month): SiteQualityScore
    {
        $ponctualite = $this->calculatePonctualite($siteId, $month);
        $qualiteService = $this->calculateQualiteService($siteId, $month);
        $satisfactionClient = $this->calculateSatisfactionClient($siteId, $month);
        $conformiteSLA = $this->calculateConformiteSLA($siteId, $month);
        
        $totalScore = ($ponctualite + $qualiteService + $satisfactionClient + $conformiteSLA) / 4;
        
        $grade = match(true) {
            $totalScore >= 90 => 'A',
            $totalScore >= 80 => 'B',
            $totalScore >= 70 => 'C',
            $totalScore >= 60 => 'D',
            default => 'F',
        };
        
        return SiteQualityScore::create([
            'score_id' => Str::uuid(),
            'site_id' => $siteId,
            'period_date' => $month->startOfMonth(),
            'period_type' => 'month',
            'ponctualite_score' => $ponctualite,
            'qualite_service_score' => $qualiteService,
            'satisfaction_client_score' => $satisfactionClient,
            'conformite_sla_score' => $conformiteSLA,
            'total_score' => $totalScore,
            'grade' => $grade,
        ]);
    }
    
    private function calculatePonctualite(string $siteId, Carbon $month): float
    {
        $total = Collecte::where('site_id', $siteId)
            ->whereMonth('planned_at', $month->month)
            ->whereYear('planned_at', $month->year)
            ->count();
        
        if ($total === 0) return 0;
        
        $onTime = Collecte::where('site_id', $siteId)
            ->whereMonth('planned_at', $month->month)
            ->whereYear('planned_at', $month->year)
            ->whereNotNull('completed_at')
            ->whereRaw('completed_at <= planned_at')
            ->count();
        
        return round(($onTime / $total) * 100, 2);
    }
    
    private function calculateSatisfactionClient(string $siteId, Carbon $month): float
    {
        $avgScore = Reclamation::where('site_id', $siteId)
            ->whereMonth('resolved_at', $month->month)
            ->whereYear('resolved_at', $month->year)
            ->whereNotNull('satisfaction_score')
            ->avg('satisfaction_score');
        
        return $avgScore ? round(($avgScore / 5) * 100, 2) : 0;
    }
}

// app/Services/SurveyService.php
class SurveyService
{
    public function create(array $data): SatisfactionSurvey
    {
        return SatisfactionSurvey::create([
            'survey_id' => Str::uuid(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'site_id' => $data['site_id'] ?? null,
            'questions' => $data['questions'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'created_by' => auth()->id(),
        ]);
    }
    
    public function submitResponse(string $surveyId, array $answers, ?string $comments = null): SurveyResponse
    {
        $survey = SatisfactionSurvey::findOrFail($surveyId);
        
        return SurveyResponse::create([
            'response_id' => Str::uuid(),
            'survey_id' => $surveyId,
            'user_id' => $survey->is_anonymous ? null : auth()->id(),
            'answers' => $answers,
            'comments' => $comments,
            'ip_address' => request()->ip(),
        ]);
    }
    
    public function getResults(string $surveyId): array
    {
        $survey = SatisfactionSurvey::with('responses')->findOrFail($surveyId);
        
        $results = [];
        
        foreach ($survey->questions as $index => $question) {
            $questionResults = [
                'question' => $question['question'],
                'type' => $question['type'], // rating, multiple_choice, text
                'responses' => [],
            ];
            
            foreach ($survey->responses as $response) {
                $answer = $response->answers[$index] ?? null;
                
                if ($question['type'] === 'rating') {
                    $questionResults['responses'][] = $answer;
                } elseif ($question['type'] === 'multiple_choice') {
                    if (!isset($questionResults['counts'][$answer])) {
                        $questionResults['counts'][$answer] = 0;
                    }
                    $questionResults['counts'][$answer]++;
                }
            }
            
            if ($question['type'] === 'rating') {
                $questionResults['average'] = collect($questionResults['responses'])->avg();
            }
            
            $results[] = $questionResults;
        }
        
        return [
            'survey' => $survey,
            'total_responses' => $survey->responses->count(),
            'results' => $results,
        ];
    }
}
```

---

## 🎨 DESIGN SYSTEM COMPLET - CSS Premium

Créons maintenant le design system complet :

```css
/* public/backend/assets/css/premium-design.css */

/* ============================================
   VARIABLES & RESET
   ============================================ */

:root {
  /* Couleurs primaires */
  --primary: #667eea;
  --primary-dark: #5568d3;
  --primary-light: #8c9eff;
  --secondary: #764ba2;
  --accent: #f093fb;
  
  /* Gradients */
  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
  --gradient-danger: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  --gradient-info: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  
  /* Neutres */
  --dark-900: #0f172a;
  --dark-800: #1e293b;
  --dark-700: #334155;
  --gray-600: #475569;
  --gray-500: #64748b;
  --gray-400: #94a3b8;
  --gray-300: #cbd5e1;
  --gray-200: #e2e8f0;
  --gray-100: #f1f5f9;
  --white: #ffffff;
  
  /* Glassmorphism */
  --glass-bg: rgba(255, 255, 255, 0.1);
  --glass-border: rgba(255, 255, 255, 0.2);
  --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
  
  /* Ombres */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
  --shadow-2xl: 0 25px 50px rgba(0, 0, 0, 0.25);
  
  /* Transitions */
  --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-base: 300ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
  
  /* Spacing */
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
  --spacing-2xl: 3rem;
}

/* ============================================
   CARDS PREMIUM
   ============================================ */

.premium-card {
  background: var(--white);
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  transition: all var(--transition-base);
  overflow: hidden;
  border: 1px solid var(--gray-200);
}

.premium-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}

.premium-card-glass {
  background: var(--glass-bg);
  backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
}

.premium-card-gradient {
  background: var(--gradient-primary);
  color: var(--white);
}

/* Card Header */
.premium-card-header {
  padding: var(--spacing-lg);
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.premium-card-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--dark-900);
  margin: 0;
}

/* Card Body */
.premium-card-body {
  padding: var(--spacing-lg);
}

/* ============================================
   BUTTONS PREMIUM
   ============================================ */

.btn-gradient-primary {
  background: var(--gradient-primary);
  border: none;
  color: var(--white);
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 600;
  box-shadow: var(--shadow-md);
  transition: all var(--transition-base);
  position: relative;
  overflow: hidden;
}

.btn-gradient-primary::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s;
}

.btn-gradient-primary:hover::before {
  left: 100%;
}

.btn-gradient-primary:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.btn-gradient-success {
  background: var(--gradient-success);
}

.btn-gradient-danger {
  background: var(--gradient-danger);
}

.btn-gradient-warning {
  background: var(--gradient-warning);
}

.btn-gradient-info {
  background: var(--gradient-info);
}

/* ============================================
   ANIMATIONS
   ============================================ */

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInRight {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

@keyframes shimmer {
  0% {
    background-position: -1000px 0;
  }
  100% {
    background-position: 1000px 0;
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out;
}

.animate-fade-in-right {
  animation: fadeInRight 0.6s ease-out;
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* ============================================
   STATS CARDS
   ============================================ */

.stats-card {
  background: var(--white);
  border-radius: 16px;
  padding: var(--spacing-lg);
  box-shadow: var(--shadow-md);
  transition: all var(--transition-base);
  border-left: 4px solid var(--primary);
}

.stats-card:hover {
  transform: translateX(4px);
  box-shadow: var(--shadow-lg);
}

.stats-card-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  margin-bottom: var(--spacing-md);
}

.stats-card-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark-900);
  margin: 0;
}

.stats-card-label {
  font-size: 0.875rem;
  color: var(--gray-600);
  margin-top: var(--spacing-xs);
}

.stats-card-trend {
  display: inline-flex;
  align-items: center;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  margin-top: var(--spacing-sm);
}

.trend-up {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.trend-down {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

/* ============================================
   BADGES PREMIUM
   ============================================ */

.badge-modern {
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-glow {
  box-shadow: 0 0 20px currentColor;
}

/* ============================================
   FORMS PREMIUM
   ============================================ */

.form-control-modern {
  border: 2px solid var(--gray-300);
  border-radius: 12px;
  padding: 12px 16px;
  transition: all var(--transition-base);
  font-size: 0.95rem;
}

.form-control-modern:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  outline: none;
}

.input-group-modern {
  position: relative;
}

.input-group-modern-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray-500);
  pointer-events: none;
}

.input-group-modern .form-control-modern {
  padding-left: 45px;
}

/* ============================================
   TABLES PREMIUM
   ============================================ */

.table-premium {
  border-collapse: separate;
  border-spacing: 0 8px;
}

.table-premium thead th {
  background: var(--gradient-primary);
  color: var(--white);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  padding: 16px;
  border: none;
}

.table-premium thead th:first-child {
  border-radius: 12px 0 0 12px;
}

.table-premium thead th:last-child {
  border-radius: 0 12px 12px 0;
}

.table-premium tbody tr {
  background: var(--white);
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base);
}

.table-premium tbody tr:hover {
  transform: scale(1.02);
  box-shadow: var(--shadow-md);
}

.table-premium tbody td {
  padding: 16px;
  border: none;
  vertical-align: middle;
}

.table-premium tbody tr td:first-child {
  border-radius: 12px 0 0 12px;
}

.table-premium tbody tr td:last-child {
  border-radius: 0 12px 12px 0;
}

/* ============================================
   MODALS PREMIUM
   ============================================ */

.modal-premium .modal-content {
  border: none;
  border-radius: 20px;
  box-shadow: var(--shadow-2xl);
  overflow: hidden;
}

.modal-premium .modal-header {
  background: var(--gradient-primary);
  color: var(--white);
  border: none;
  padding: var(--spacing-lg) var(--spacing-xl);
}

.modal-premium .modal-body {
  padding: var(--spacing-xl);
}

.modal-premium .modal-backdrop {
  backdrop-filter: blur(10px);
}

/* ============================================
   SIDEBAR PREMIUM
   ============================================ */

.sidebar-premium {
  background: var(--dark-900);
  color: var(--white);
  min-height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  width: 280px;
  padding: var(--spacing-lg);
  box-shadow: var(--shadow-xl);
  z-index: 1000;
}

.sidebar-premium-logo {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: var(--spacing-xl);
  text-align: center;
  padding: var(--spacing-lg);
  background: var(--gradient-primary);
  border-radius: 12px;
}

.sidebar-premium-nav {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-premium-nav-item {
  margin-bottom: var(--spacing-sm);
}

.sidebar-premium-nav-link {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-radius: 12px;
  color: var(--gray-400);
  text-decoration: none;
  transition: all var(--transition-base);
  position: relative;
  overflow: hidden;
}

.sidebar-premium-nav-link::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 4px;
  height: 100%;
  background: var(--gradient-primary);
  transform: scaleY(0);
  transition: transform var(--transition-base);
}

.sidebar-premium-nav-link:hover,
.sidebar-premium-nav-link.active {
  background: rgba(102, 126, 234, 0.1);
  color: var(--white);
}

.sidebar-premium-nav-link:hover::before,
.sidebar-premium-nav-link.active::before {
  transform: scaleY(1);
}

.sidebar-premium-nav-icon {
  margin-right: var(--spacing-md);
  font-size: 1.25rem;
}

/* ============================================
   HEADER PREMIUM
   ============================================ */

.header-premium {
  background: var(--white);
  box-shadow: var(--shadow-md);
  padding: var(--spacing-md) var(--spacing-xl);
  position: sticky;
  top: 0;
  z-index: 999;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-premium-search {
  flex: 1;
  max-width: 500px;
  margin: 0 var(--spacing-xl);
}

.header-premium-actions {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.notification-bell {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--gray-100);
  cursor: pointer;
  transition: all var(--transition-base);
}

.notification-bell:hover {
  background: var(--primary);
  color: var(--white);
  transform: scale(1.1);
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--gradient-danger);
  color: var(--white);
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
}

/* ============================================
   LOADING STATES
   ============================================ */

.skeleton {
  background: linear-gradient(
    90deg,
    var(--gray-200) 25%,
    var(--gray-300) 50%,
    var(--gray-200) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 2s infinite;
  border-radius: 8px;
}

.spinner-modern {
  width: 40px;
  height: 40px;
  border: 4px solid var(--gray-200);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ============================================
   RESPONSIVE
   ============================================ */

@media (max-width: 768px) {
  .sidebar-premium {
    transform: translateX(-100%);
    transition: transform var(--transition-base);
  }
  
  .sidebar-premium.active {
    transform: translateX(0);
  }
  
  .premium-card {
    margin-bottom: var(--spacing-md);
  }
  
  .table-premium {
    font-size: 0.875rem;
  }
}
```

---

**RÉSUMÉ COMPLET:**

J'ai créé pour vous :

1. ✅ **Architecture complète** des 10 modules
2. ✅ **Schémas de base de données** détaillés
3. ✅ **Services métier** pour chaque fonctionnalité
4. ✅ **Design system premium** moderne et professionnel

**Prochaines étapes suggérées:**

1. Créer les migrations pour les nouvelles tables
2. Implémenter les contrôleurs et routes
3. Créer les vues Blade avec le design premium
4. Intégrer les API externes (MTN Mobile Money, WhatsApp, etc.)
5. Mettre en place les jobs et queues pour les tâches asynchrones
6. Implémenter les tests unitaires

Voulez-vous que je commence l'implémentation d'un module spécifique ou que je crée un fichier de roadmap détaillé ?
