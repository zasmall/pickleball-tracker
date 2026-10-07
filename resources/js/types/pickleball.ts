export type GameFormat = 'singles' | 'doubles';

export type Team = 'a' | 'b';

export type PlayerOption = {
    id: number;
    name: string;
};

export type Game = {
    id: number;
    played_on: string;
    format: GameFormat;
    location: string | null;
    notes: string | null;
    team_a: PlayerOption[];
    team_b: PlayerOption[];
    team_a_score: number;
    team_b_score: number;
    winner: Team;
};

export type PlayerRecord = {
    played: number;
    wins: number;
    losses: number;
    win_rate: number;
    points_for: number;
    points_against: number;
    point_diff: number;
};

export type LeaderboardRow = PlayerRecord & {
    id: number;
    name: string;
    streak: string | null;
};

export type HeadToHead = {
    id: number;
    name: string;
    played: number;
    wins: number;
    losses: number;
    win_rate: number;
};

export type PlayerStats = {
    overall: PlayerRecord;
    singles: PlayerRecord;
    doubles: PlayerRecord;
    streak: string | null;
    partners: HeadToHead[];
    opponents: HeadToHead[];
};
