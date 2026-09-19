# Offline POS Sync

This setup lets the shop keep selling from the local XAMPP copy when internet is down, then push POS sales to the hosted Hostinger copy later.

## Local XAMPP Copy

Set these in `.env`:

```env
OFFLINE_POS_MODE=local
OFFLINE_POS_REMOTE_URL=https://your-real-hostinger-domain.com
OFFLINE_POS_SYNC_TOKEN=the-same-secret-token-used-on-hostinger
OFFLINE_POS_SOURCE_ID=ONJECASA-LOCAL-XAMPP
```

Run pending sync manually:

```bash
php artisan pos:sync-offline-sales
```

To automate it on the shop computer, run Laravel's scheduler every minute with Windows Task Scheduler:

```bash
php C:\xampp\htdocs\ONJECASAFoodAndSaladBar\artisan schedule:run
```

## Hostinger Copy

Set these in Hostinger `.env`:

```env
OFFLINE_POS_MODE=hosted
OFFLINE_POS_SYNC_TOKEN=the-same-secret-token-used-on-local-xampp
OFFLINE_POS_SOURCE_ID=ONJECASA-HOSTED
```

The hosted copy receives synced sales at:

```text
POST /api/pos/offline-sales
```

Sales are matched idempotently by `source_uuid`, and sale items are matched to hosted products by product barcode/code.
