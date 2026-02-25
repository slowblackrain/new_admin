<?php
$envPath = __DIR__ . '/.env';
$content = "\n\n# TOSS PAYMENTS (GENERAL)\n"
    . "TOSS_PAYMENTS_R_CLIENT_ID=live_ck_GjLJoQ1aVZJgxo0KL9QVw6KYe2RN\n"
    . "TOSS_PAYMENTS_R_SECRET_KEY=live_sk_ALnQvDd2VJYn056225a3Mj7X41mN\n"
    . "TOSS_PAYMENTS_TR_CLIENT_ID=test_ck_Z1aOwX7K8meR6L0pQQQ8yQxzvNPG\n"
    . "TOSS_PAYMENTS_TR_SECRET_KEY=test_sk_5OWRapdA8dYBo2Oz7lP3o1zEqZKL\n\n"
    . "# TOSS PAYMENTS (EMONEY YUM/RECHARGE)\n"
    . "TOSS_PAYMENTS_M_CLIENT_ID=live_ck_6BYq7GWPVv5XdB0PXgL3NE5vbo1d\n"
    . "TOSS_PAYMENTS_M_SECRET_KEY=live_sk_DnyRpQWGrNmQ0YKjRJ0VKwv1M9EN\n"
    . "TOSS_PAYMENTS_TM_CLIENT_ID=test_ck_6bJXmgo28edRQjoXdLwrLAnGKWx4\n"
    . "TOSS_PAYMENTS_TM_SECRET_KEY=test_sk_yZqmkKeP8gPbO1nRkoj3bQRxB9lG\n\n"
    . "# PAIRING (CKER) PG\n"
    . "PAIRING_CLIENT_ID=23050362\n"
    . "PAIRING_TEST_CLIENT_ID=23049615\n";

file_put_contents($envPath, $content, FILE_APPEND);
echo "Appended PG Keys to .env\n";
