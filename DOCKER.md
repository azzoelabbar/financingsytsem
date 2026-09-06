# ميزان — التشغيل / Running Mizan

النظام كله داخل حاوية واحدة. لا حاجة لتثبيت PHP أو قاعدة بيانات.
The whole system runs in one container. Nothing else to install.

---

## التشغيل بأمر واحد / Run it with one command

بعد نشر الصورة (انظر [النشر](#النشر--publishing-the-image) أدناه) يستطيع أي شخص تشغيل
النظام دون نسخة من المشروع — تماماً كما تفعل أدوات مثل Stirling PDF:
Once the image is published (see [Publishing](#النشر--publishing-the-image)
below) anyone can run the system without a copy of this project — exactly the
way tools like Stirling PDF are run:

```bash
docker run -d --name mizan -p 8080:8080 \
  -v mizan-data:/data \
  -v mizan-storage:/app/storage \
  --restart unless-stopped \
  ghcr.io/YOUR-GITHUB-USERNAME/mizan:latest
```

ثم افتح **<http://localhost:8080>**.
Then open **<http://localhost:8080>**.

الحاوية تهيّئ نفسها بالكامل عند أول تشغيل: تنشئ قاعدة البيانات، تطبّق الترحيلات،
وتضيف العملات. لا أوامر أخرى.
The container sets itself up on first boot: it creates the database, runs the
migrations and installs the currencies. Nothing else to run.

> **`-v mizan-data:/data` ليس اختيارياً.** بدونه تختفي الحسابات والدفاتر عند حذف الحاوية.
> **`-v mizan-data:/data` is not optional.** Without it the accounts and the
> books disappear the moment the container is removed.

إذا كان المنفذ 8080 مستخدماً، غيّر الرقم الأيسر فقط وأضف العنوان:
If port 8080 is taken, change only the left-hand number and pass the address:

```bash
docker run -d --name mizan -p 9000:8080 \
  -e APP_URL=http://localhost:9000 \
  -v mizan-data:/data -v mizan-storage:/app/storage \
  --restart unless-stopped \
  ghcr.io/YOUR-GITHUB-USERNAME/mizan:latest
```

للتحديث لاحقاً / To update later:

```bash
docker pull ghcr.io/YOUR-GITHUB-USERNAME/mizan:latest
docker rm -f mizan
# ثم أعد أمر التشغيل نفسه / then run the same command again
```

البيانات في وحدات التخزين، فلا تتأثر بحذف الحاوية.
The data lives in the volumes, so removing the container does not touch it.

---

## النشر / Publishing the image

ملف [`.github/workflows/publish-image.yml`](.github/workflows/publish-image.yml)
ينشر الصورة تلقائياً لـ `linux/amd64` و`linux/arm64` (بما فيها أجهزة آبل الحديثة).
The workflow at [`.github/workflows/publish-image.yml`](.github/workflows/publish-image.yml)
publishes the image automatically for both `linux/amd64` and `linux/arm64`
(Apple Silicon included).

1. ارفع المشروع إلى GitHub.
   Push this project to GitHub.
2. سيعمل الإجراء عند كل دفع إلى `main` وينشر الصورة على:
   The workflow runs on every push to `main` and publishes to:

   `ghcr.io/<اسم-حسابك>/mizan:latest`

3. اجعل الحزمة عامة مرة واحدة من صفحة المستودع → **Packages → mizan → Package settings → Change visibility → Public**، حتى لا يحتاج المستخدمون لتسجيل الدخول.
   Make the package public once, under the repository's **Packages → mizan →
   Package settings → Change visibility → Public**, so users do not need to log in.

لا حاجة لإضافة أي مفتاح سري: GitHub يوفّر الصلاحية تلقائياً.
No secret to add: GitHub supplies the credential itself.

### النشر يدوياً إلى Docker Hub / Publishing manually to Docker Hub

إذا فضّلت Docker Hub، سجّل الدخول بنفسك ثم:
If you prefer Docker Hub, sign in yourself and then:

```bash
docker login
docker buildx create --use --name mizan-builder
docker buildx build --platform linux/amd64,linux/arm64 \
  -t YOUR-DOCKERHUB-USERNAME/mizan:latest --push .
```

فيصبح أمر التشغيل عند الآخرين:
People then run:

```bash
docker run -d -p 8080:8080 -v mizan-data:/data \
  YOUR-DOCKERHUB-USERNAME/mizan
```

---

## أول مرة / First time

1. ثبّت **Docker Desktop** من <https://www.docker.com/products/docker-desktop/> وافتحه وانتظر حتى تظهر عبارة *Engine running*.
   Install **Docker Desktop**, open it, wait until it says *Engine running*.
2. انقر نقراً مزدوجاً على **`start-mizan.bat`** (على ويندوز) أو شغّل `./start-mizan.sh` (على ماك ولينكس).
   Double-click **`start-mizan.bat`** (Windows) or run `./start-mizan.sh` (macOS/Linux).
3. المرة الأولى تستغرق عدة دقائق لأنها تبني النظام. بعدها يفتح المتصفح على:
   The first run takes a few minutes while it builds. Then your browser opens at:

   **<http://localhost:8080>**

4. ستظهر **استمارة إنشاء الحساب مرة واحدة فقط**. أنشئ حساب المالك، ثم أكمل خطوتي إعداد الشركة.
   You get the **one-time registration form**. Create the owner account, then finish the two setup steps.

بعد إنشاء الحساب لا يمكن إنشاء حساب آخر — تسجيل الدخول فقط.
After that no second account can ever be created — sign-in only.

---

## الاستخدام اليومي / Every day

لا شيء. النظام يعمل في الخلفية ويعيد تشغيل نفسه تلقائياً عند تشغيل الجهاز.
Nothing. It runs in the background and starts itself whenever the computer is
switched on. Just open <http://localhost:8080>.

> اجعل Docker Desktop يبدأ مع تشغيل الجهاز: *Settings → General → Start Docker Desktop when you sign in*.
> Turn on *Settings → General → Start Docker Desktop when you sign in* so the
> system is up before anyone needs it.

| الملف / File | ما يفعله / What it does |
|---|---|
| `start-mizan.bat` | يشغّل النظام ويفتح المتصفح — Starts it and opens the browser |
| `stop-mizan.bat` | يوقف النظام (البيانات تبقى محفوظة) — Stops it; data is kept |
| `show-logs.bat` | يعرض السجل عند وجود مشكلة — Shows the log when something looks wrong |
| `backup-mizan.bat` | ينسخ قاعدة البيانات إلى مجلد `backups` — Copies the database into `backups` |
| `share-link.bat` | ينشئ رابطاً للمشاركة من أي مكان — Creates a link others can open from anywhere |
| `stop-sharing.bat` | يوقف رابط المشاركة — Switches the shared link off |

---

## ماذا يحدث عند كل تشغيل / What happens on every start

الحاوية تنفّذ كل ما يلزم بنفسها:
The container does all of this by itself:

1. تنشئ مفتاح التطبيق وتحفظه (حتى لا تنقطع الجلسات) — creates and keeps the application key
2. تنشئ ملف قاعدة البيانات إن لم يكن موجوداً — creates the database file if missing
3. `php artisan migrate --force` — تطبّق تحديثات قاعدة البيانات
4. `php artisan db:seed --class=AccountingReferenceSeeder --force` — العملات والبيانات المرجعية
5. `storage:link` ثم `config:cache`, `route:cache`, `view:cache`, `event:cache` — التهيئة والتسريع

كل الخطوات آمنة عند التكرار، فلا ضرر من إعادة التشغيل في أي وقت.
Every step is safe to repeat, so restarting is always harmless.

---

## مشاركة النظام برابط / Sharing the system by link

لا حاجة لإعطاء أحد مجلد المشروع. النظام يعمل على جهاز واحد، والباقون يفتحونه برابط.
Nobody else needs the project folder. The system runs on one machine and
everyone else opens it with a link. Three ways, from simplest to best:

### ١ — على شبكة المكتب / On the office network

الأبسط والأأمن. اعرف عنوان الجهاز:
The simplest and safest option. Find the machine's address:

```bash
ipconfig
```

خذ `IPv4 Address` (مثلاً `192.168.1.50`) وأعطِ الناس:
Take the `IPv4 Address` (e.g. `192.168.1.50`) and give people:

**`http://192.168.1.50:8080`**

ثم في `docker-compose.yml` اجعل `APP_URL` مطابقاً، وأعد التشغيل، واسمح للمنفذ 8080
في جدار حماية ويندوز.
Then set `APP_URL` in `docker-compose.yml` to the same address, restart, and
allow port 8080 through the Windows firewall.

لا يعمل خارج المكتب. / It does not work outside the office.

### ٢ — رابط مؤقت من أي مكان / A temporary link from anywhere

انقر نقراً مزدوجاً على **`share-link.bat`**. سيظهر عنوان مثل:
Double-click **`share-link.bat`**. You get an address like:

**`https://mizan-brave-tiger.trycloudflare.com`**

بروتوكول HTTPS، ولا يحتاج حساباً ولا نطاقاً. يعمل من أي مكان في العالم.
HTTPS, no account and no domain needed, reachable from anywhere.

- يتغير العنوان في كل مرة تشغّل فيها الملف / the address changes every run
- يعمل فقط أثناء تشغيل الجهاز والنظام / it works only while this machine and Mizan are running
- لإيقافه: **`stop-sharing.bat`** / to switch it off: **`stop-sharing.bat`**

مناسب للعرض أو الاستخدام المؤقت. / Good for a demo or a short hand-off.

### ٣ — رابط ثابت على نطاقك / A permanent link on your own domain

مثل `https://mizan.yourcompany.com` — عنوان لا يتغير، وهو الخيار الصحيح للاستخدام اليومي.
Like `https://mizan.yourcompany.com` — an address that never changes. This is
the right option for daily use.

1. أنشئ حساباً مجانياً على Cloudflare وأضف نطاقك.
   Create a free Cloudflare account and add your domain.
2. من <https://one.dash.cloudflare.com> → **Networks → Tunnels → Create a tunnel**.
3. اختر **Docker** كطريقة التثبيت وانسخ الرمز (يبدأ بـ `eyJ`).
   Choose **Docker** as the connector and copy the token (it starts with `eyJ`).
4. في **Public hostname** اختر النطاق الفرعي، واجعل الخدمة `http://mizan:8080`.
   Under **Public hostname**, pick the subdomain and set the service to `http://mizan:8080`.
5. ضع الرمز في ملف `.env` بجوار `docker-compose.yml`:
   Put the token in a `.env` file next to `docker-compose.yml`:

   ```dotenv
   CLOUDFLARE_TUNNEL_TOKEN=eyJhIjoi…
   ```

6. اجعل `APP_URL` في `docker-compose.yml` هو العنوان الكامل، ثم شغّل:
   Set `APP_URL` in `docker-compose.yml` to the full address, then run:

   ```bash
   docker compose --profile link up -d
   ```

الرابط يعمل تلقائياً بعد ذلك في كل مرة يعمل فيها الجهاز.
From then on the link comes back by itself whenever the machine is on.

### ⚠ قبل أن تشارك الرابط / Before you share the link

- **أنشئ حساب المالك أولاً.** النظام يسمح بحساب واحد، ومن يفتح الرابط قبلك على نظام
  جديد يستطيع أخذه.
  **Register the owner account first.** This install allows exactly one account,
  and on a fresh system whoever opens the link before you can claim it.
- من يملك الرابط يصل إلى صفحة الدخول فقط، لكنه يصل إليها من أي مكان — استخدم كلمة
  مرور قوية، وفعّل المصادقة الثنائية من الإعدادات.
  Anyone with the link reaches the sign-in page from anywhere — use a strong
  password and turn on two-factor authentication in the settings.
- لطبقة حماية إضافية على الرابط الثابت، فعّل **Cloudflare Access** على النطاق الفرعي
  ليطلب تسجيل دخول قبل الوصول إلى النظام أصلاً.
  For an extra layer on the permanent link, put **Cloudflare Access** in front of
  the hostname so visitors must authenticate before they even reach Mizan.
- الوصول من الشبكة المحلية فقط (الخيار ١) يبقى الأأمن.
  Local-network-only (option 1) remains the safest.

---

## البيانات والنسخ الاحتياطي / Data and backups

البيانات في وحدتي تخزين (Docker volumes):
The data lives in two Docker volumes:

- `mizan-data` — قاعدة البيانات ومفتاح التطبيق / the database and the application key
- `mizan-storage` — الملفات المرفوعة والمستندات / uploaded files and documents

`docker compose down` لا يحذفها. الأمر الوحيد الذي يمحوها هو `docker compose down -v` — **لا تستخدمه**.
`docker compose down` keeps them. Only `docker compose down -v` erases them —
**do not run that.**

شغّل `backup-mizan.bat` بانتظام واحفظ الملف الناتج خارج الجهاز.
Run `backup-mizan.bat` regularly and keep the resulting file somewhere else.

للاستعادة / To restore:

```bash
docker compose up -d
docker compose exec -T mizan sh -c "cd /data && tar -xf -" < backups\mizan-XXXX.tar
docker compose restart mizan
```

---

## التحديث / Updating

بعد أي تغيير في الكود:
After the code changes:

```bash
docker compose up -d --build
```

الترحيلات والبيانات المرجعية تُطبَّق تلقائياً عند الإقلاع. بياناتك تبقى كما هي.
Migrations and reference data are applied automatically on boot. Your data is untouched.

---

## إعدادات يمكن تغييرها / Settings you may want to change

حرّرها مباشرة في `docker-compose.yml`.
Edit them directly in `docker-compose.yml`.

إذا كان المنفذ 8080 مستخدماً، غيّر الرقم الأيسر فقط ثم عدّل `APP_URL` ليطابقه:
If port 8080 is taken, change only the left-hand number, then make `APP_URL` match:

```yaml
    ports:
      - "8080:80"
    environment:
      APP_URL: "http://localhost:8080"
```

لتشغيله على شبكة المكتب، ضع عنوان الجهاز في `MIZAN_URL`
(مثلاً `http://192.168.1.50:8080`) وافتح المنفذ في جدار الحماية.
To serve it on the office network, put the machine's address in `MIZAN_URL`
(e.g. `http://192.168.1.50:8080`) and open that port in the firewall.

> النظام يعمل عبر HTTP عادي. إذا خرج خارج الشبكة المحلية، ضع أمامه وسيطاً عكسياً
> (Caddy / Nginx / Cloudflare Tunnel) يتولى شهادة HTTPS.
> It serves plain HTTP. If it is reachable beyond the local network, put a
> reverse proxy in front of it to terminate HTTPS.

---

## قاعدة بيانات خارجية (اختياري) / An external database (optional)

الوضع الافتراضي SQLite: ملف واحد، بلا خادم ولا كلمات مرور، ومناسب تماماً لمستخدم واحد.
The default is SQLite: one file, no server, no passwords — right for a single user.

للانتقال إلى MySQL أو PostgreSQL، غيّر متغيرات البيئة في `docker-compose.yml`:
To move to MySQL or PostgreSQL, change the environment block in `docker-compose.yml`:

```yaml
DB_CONNECTION: mysql
DB_HOST: db
DB_PORT: "3306"
DB_DATABASE: mizan
DB_USERNAME: mizan
DB_PASSWORD: "…"
```

سكربت الإقلاع ينتظر الخادم حتى يستجيب قبل تطبيق الترحيلات.
The start-up script waits for the server to answer before it migrates.

---

## عند حدوث مشكلة / If something goes wrong

1. `show-logs.bat` — السطر الأخير يشرح المشكلة عادةً / the last lines usually say why
2. `stop-mizan.bat` ثم `start-mizan.bat` — إعادة التشغيل تحل أغلب الحالات / a restart fixes most things
3. تأكد أن Docker Desktop يعمل / make sure Docker Desktop is running

لا تشغّل `php artisan db:seed` وحده داخل الحاوية: البذرة الافتراضية تنشئ حساباً تجريبياً
وبيانات وهمية، وقد يشغل ذلك الحساب الوحيد المسموح به.
Never run a bare `php artisan db:seed` inside the container — the default seeder
creates a demo account and demo books, and that demo account would take the one
account slot this install allows.
