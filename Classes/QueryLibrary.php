<?php

class QueryLibrary {

	static function LatestFiles() {
		return '
			SELECT DISTINCT f.ID, f.Title
			FROM Groups g
				JOIN Pairs p ON p.GroupID = g.ID
				JOIN Dates d ON d.ID = p.DateID
				JOIN Files f ON f.ID = p.FileID
			WHERE f.ID = (
				SELECT MAX(pi.FileID)
				FROM Pairs pi
				WHERE pi.GroupID = p.GroupID
					AND pi.DateID = p.DateID
			)
			ORDER BY d.Date
		';
	}

	static function AllDays() {
		return "
			SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
				CONCAT('Вместе с ', (
				SELECT GROUP_CONCAT(wg.Title SEPARATOR ', ') FROM Withs w
				JOIN Groups wg ON wg.ID = w.GroupID
				WHERE w.PairID = p.ID
				)) as `With`
			FROM Pairs p
				JOIN Times t ON t.ID = p.TimeID
				JOIN Styles s ON s.ID = p.StyleID
				JOIN Dates d ON d.ID = p.DateID
				JOIN Files f ON f.ID = p.FileID
			WHERE p.GroupID = :group
				AND d.Date >= DATE(NOW())
				AND p.FileID = (
					SELECT MAX(pi.FileID)
					FROM Pairs pi
						JOIN Dates di ON di.ID = pi.DateID
					WHERE pi.GroupID = p.GroupID
						AND pi.DateID = p.DateID
				)
			ORDER BY d.Date, t.Number
		";
	}

	static function AllDaysForTeacher() {
		$separator = '<br>';
		return "
			SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName,
				CONCAT_WS('$separator', g.Title, (
				SELECT GROUP_CONCAT(wg.Title SEPARATOR '$separator') FROM Withs w
				JOIN Groups wg ON wg.ID = w.GroupID
				WHERE w.PairID = p.ID
				)) as `With`
			FROM Pairs p
				JOIN Groups g ON g.ID = p.GroupID
				JOIN Times t ON t.ID = p.TimeID
				JOIN Styles s ON s.ID = p.StyleID
				JOIN Dates d ON d.ID = p.DateID
				JOIN Files f ON f.ID = p.FileID
			WHERE p.Title LIKE :teacher
				AND d.Date >= DATE(NOW())
				AND p.FileID = (
					SELECT MAX(pi.FileID)
					FROM Pairs pi
						JOIN Dates di ON di.ID = pi.DateID
					WHERE pi.GroupID = p.GroupID
						AND pi.DateID = p.DateID
				)
			GROUP BY t.Number, t.Time, p.Title, s.Style, d.Date, d.Dow, f.Title
			ORDER BY d.Date, t.Number
		";
	}

	static function DaysForFile() {
		return "
			SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
				CONCAT('Вместе с ', (
				SELECT GROUP_CONCAT(wg.Title SEPARATOR ', ') FROM Withs w
				JOIN Groups wg ON wg.ID = w.GroupID
				WHERE w.PairID = p.ID
				)) as `With`
			FROM Pairs p
				JOIN Times t ON t.ID = p.TimeID
				JOIN Styles s ON s.ID = p.StyleID
				JOIN Dates d ON d.ID = p.DateID
				JOIN Files f ON f.ID = p.FileID
			WHERE p.GroupID = :group
				AND p.FileID = :file
			ORDER BY d.Date, t.Number
		";
	}

	static function FewDays($days = 6) {
		$days = intval($days);
		return "
			SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
				CONCAT('Вместе с ', (
				SELECT GROUP_CONCAT(wg.Title SEPARATOR ', ') FROM Withs w
				JOIN Groups wg ON wg.ID = w.GroupID
				WHERE w.PairID = p.ID
				)) as `With`
			FROM Pairs p
				JOIN Times t ON t.ID = p.TimeID
				JOIN Styles s ON s.ID = p.StyleID
				JOIN Dates d ON d.ID = p.DateID
				JOIN Files f ON f.ID = p.FileID
			WHERE p.GroupID = :group
				AND d.Date >= DATE(NOW())
				AND d.Date <= DATE(NOW() + INTERVAL $days DAY)
				AND p.FileID = (
					SELECT MAX(pi.FileID)
					FROM Pairs pi
						JOIN Dates di ON di.ID = pi.DateID
					WHERE pi.GroupID = p.GroupID
						AND pi.DateID = p.DateID
				)
			ORDER BY d.Date, t.Number
		";
	}

}
