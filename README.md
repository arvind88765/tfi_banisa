# Pattukunte Pattucheera — Daily Movie Guessing Game

A Heardle/Wordle-style daily movie game with 3 modes:
- **Audio** — up to 10 clips per movie, each wrong guess reveals the next clip
- **Picture** — 3 images, revealed one at a time per wrong guess
- **Music** — one soundtrack clip, multiple tries

One round is featured per day (same for every visitor), with a shareable
Wordle-style result grid.

## 1. Push this to GitHub

From your own computer, in this project folder:

```bash
git init
git remote add origin https://github.com/arvind88765/tfi_banisa.git
git add .
git commit -m "Initial project"
git branch -M main
git push -u origin main
```

⚠️ **Regenerate your GitHub token before doing this.** The token you shared
earlier is now exposed in chat history — go to GitHub → Settings → Developer
settings → Personal access tokens → revoke it, then generate a fresh one for
your own use (or just use your normal GitHub login / SSH key instead of a
token, which is simpler for a one-person project).

## 2. Connect Hostinger to your GitHub repo

In hPanel:
1. Go to **Advanced → Git**
2. Connect the repository `arvind88765/tfi_banisa`, branch `main`
3. Set the deploy path to your domain's `public_html` (or a subfolder)
4. Click **Deploy**

Every time you push new code later, come back here and click **Deploy latest commit**.

## 3. Create the database

1. hPanel → **Databases → MySQL Databases** → create a new database + user, note the credentials
2. hPanel → **Databases → phpMyAdmin** → select your new database → **Import** → upload `schema.sql`

## 4. Create config.php on the server (not in GitHub)

This file holds secrets, so it's `.gitignore`d and never gets pushed. After
your first deploy:

1. Open **File Manager** in hPanel, navigate to where the site was deployed
2. Duplicate `config.sample.php`, rename the copy to `config.php`
3. Edit it and fill in:
   - Your real DB host/name/user/password from step 3
   - Your TMDb API key (free, from https://www.themoviedb.org/settings/api)
   - Your own admin username/password
   - `GAME_NAME` and `LAUNCH_DATE` (the date your Day 1 puzzle should start)

## 5. Add your first round

Visit `yourdomain.com/admin/login.php`, log in, click **+ New Round**:
- Pick a mode (Audio / Picture / Music)
- Search and select the correct movie via TMDb
- Upload the matching number of files for that mode
- Save

The public page at `yourdomain.com/` will automatically pick a round to
feature each day, rotating through whichever rounds haven't been used yet.

## Notes on hosting

- Your Premium plan is shared hosting — no ffmpeg, no shell access. That's
  why there's no video conversion step: video files are uploaded and served
  as-is, and the game plays them with the audio track only (hidden video
  element), so no conversion is ever needed.
- 20GB storage is shared across all sites on your plan. A 10-second 720p
  clip is roughly 2–4MB, so storage should last a very long time.
- If you ever want stricter security (e.g. preventing guesses via browser
  devtools from bypassing attempt limits), that would need server-side
  session tracking — ask if you want that added later.
