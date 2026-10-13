# warmth-grain

# Database updates
For an existing installation, run `database/upgrade_inquiries_events.sql` once against the `warmth_grain` database to enable customer inquiry replies and additional event photos. New installations should use `database/warmth_grain.sql`.

Account confirmation emails require a Brevo API key and verified sender. Copy `config/mail.example.php` to `config/mail.php` and fill in the sender/API key values; do not commit `mail.php`.

# DAILY WORK FLOW  
# git pull                      # 1. get latest before you start
# ...write code...
# git status                    # 2. see what changed
# git add .                     # 3. stage everything
# git commit -m "Add cart quantity controls"   # 4. save snapshot
# git push                      # 5. upload to GitHub
