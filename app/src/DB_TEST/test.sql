
use admin_console;

show tables;

select * from users;

select * from achievements;


# ゲストの参加人数が３人未満の救難信号をランダムに10件まで取得
SELECT distress_signals.id AS d_signal_id, IFNULL(cnt,0) AS cnt_guest from distress_signals
left join (SELECT COUNT(*) AS cnt, distress_signal_id FROM guests GROUP BY distress_signal_id) AS test on
test.distress_signal_id = distress_signals.id
where IFNULL(cnt,0) < 3;

# 自分が参加していない救難信号を取得
SELECT guests.user_id, d_signals.id AS d_signal_id, stage_id, action, IFNULL(cnt,0) AS cnt_guest, guests.is_getItem, d_signals.created_at FROM guests
INNER JOIN distress_signals AS d_signals ON guests.distress_signal_id = d_signals.id
LEFT JOIN (SELECT COUNT(*) AS cnt, distress_signal_id FROM guests GROUP BY distress_signal_id) AS sub_guest
ON d_signals.id = sub_guest.distress_signal_id
where guests.user_id != 1;

# ゲストの場合の救難信号ログ取得処理
SELECT d_signals.id AS d_signal_id, stage_id, action, IFNULL(cnt,0) AS cnt_guest, guests.is_getItem, d_signals.created_at FROM guests
INNER JOIN distress_signals AS d_signals ON guests.distress_signal_id = d_signals.id
LEFT JOIN (SELECT COUNT(*) AS cnt, distress_signal_id FROM guests GROUP BY distress_signal_id) AS sub_guest
ON d_signals.id = sub_guest.distress_signal_id
where guests.user_id = 1;

# アチーブメントの取得
SELECT achievements.id AS id,title,text,type,achieved_val,IFNULL(progress_val,0) AS progress_val,IFNULL(is_achieved,0) AS is_achieved FROM achievements
LEFT JOIN user_achievements AS ua ON achievements.id = ua.achievement_id
AND ua.user_id = 2;

# typeとuser_idを指定してまだ達成していないアチーブメントを取得する更新
SELECT achievements.id AS id, IFNULL(progress_val,0) AS progress_val, IFNULL(is_achieved,0) AS is_achieved FROM achievements
LEFT JOIN user_achievements AS ua ON achievements.id = ua.achievement_id
AND type = 1 AND user_id = 2 AND is_achieved = 0
ORDER BY id;

# typeとuser_idを指定してまだ達成していないアチーブメントを取得する
SELECT achievements.id AS id, achieved_val, IFNULL(progress_val,0), is_achieved AS is_arrive FROM achievements
LEFT JOIN user_achievements ON user_achievements.achievement_id = achievements.id
AND user_id = 2
WHERE type = 1 AND (is_achieved = 0 OR is_achieved IS NULL);

# [ アチーブメント更新処理 ] ####################################

# アチーブメントマスタ取得処理
SELECT * from achievements;

# アチーブメント達成状況取得




explain SELECT achievements.id AS id, progress_val FROM achievements
LEFT JOIN user_achievements AS ua ON achievements.id = ua.achievement_id
AND type = 1 AND user_id = 2 AND ua.is_achieved = 0;
