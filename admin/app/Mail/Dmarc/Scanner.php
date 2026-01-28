<?php declare(strict_types=1);

namespace App\Mail\Dmarc;

use App\Models\DmarcReport;
use App\Models\DmarcReportRecord;
use Elastic\Elasticsearch\ClientInterface as ElasticClient;
use Illuminate\Support\Carbon;
use Webklex\PHPIMAP\Client as ImapClient;

/**
 * Dmarc scanner class
 *
 * @package  App
 * @category Support
 * @author   Nguyen Van Nguyen - nguyennv@iwayvietnam.com
 */
final class Scanner
{
    const INDEX_NAME = "dmarc_aggregate";

    /**
     * Constructor
     *
     * @param ImapClient $imapClient
     * @param ElasticClient $elasticClient
     * @return self
     */
    public function __construct(
        private readonly ImapClient $imapClient,
        private readonly ?ElasticClient $elasticClient = null,
    ) {}

    /**
     * Scan dmarc report in imap folder
     *
     * @param string $reportFolder
     * @param string $archiveFolder
     * @return array
     */
    public function scan(
        string $reportFolder = "INBOX",
        string $archiveFolder = "Archive",
    ): array {
        $models = [];

        if (empty($this->imapClient->getFolderByName($archiveFolder, true))) {
            $this->imapClient->createFolder($archiveFolder, false);
        }

        $folder = $this->imapClient->getFolderByName($reportFolder, true);
        if (!empty($folder)) {
            $dmarcReports = [];
            $canMove = collect(
                $this->imapClient->getConnection()->getCapabilities()->array(),
            )->contains("MOVE");
            $messages = $folder->messages()->all()->get();
            foreach ($messages as $message) {
                $reports = Parser::parseMessage($message);
                foreach ($reports as $report) {
                    $dmarcReports[] = $report;
                    if (!empty($report->report_metadata)) {
                        $models[] = DmarcReport::where([
                            ["report_id", $report->report_metadata->report_id],
                            ["org_name", $report->report_metadata->org_name],
                        ])->firstOr(
                            static fn() => self::createDmarcReport($report),
                        );

                        if ($canMove) {
                            $message->move($archiveFolder);
                        } else {
                            try {
                                $message->copy($archiveFolder);
                            } catch (\Throwable $e) {
                                logger()->error($e);
                            }
                            $message->delete();
                        }
                    }
                }
            }

            $this->indexDmarcReports($dmarcReports);
        }

        return $models;
    }

    private static function createDmarcReport($report): array
    {
        $reports = [];

        $metadata = $report->report_metadata;
        $policy = $report->policy_published;
        $reports[] = DmarcReport::create([
            "report_id" => $metadata?->report_id ?? "",
            "org_name" => $metadata?->org_name ?? "",
            "org_email" => $metadata?->email ?? null,
            "extra_contact" => $metadata?->xtra_contact_info ?? null,
            "date_begin" => Carbon::createFromTimestampUTC(
                (int) $metadata?->date_range?->begin,
            ),
            "date_end" => Carbon::createFromTimestampUTC(
                (int) $metadata?->date_range?->end,
            ),
            "domain" => $policy?->domain ?? "",
            "adkim" => $policy?->adkim ?? "",
            "aspf" => $policy?->aspf ?? "",
            "policy" => $policy?->p ?? "",
            "subdomain_policy" => $policy?->sp ?? "",
            "percentage" => (int) ($policy?->pct ?? 0),
            "is_forensic" => (bool) ($policy?->fo ?? false),
        ]);

        if (!empty($report->records)) {
            foreach ($report->records as $record) {
                DmarcReportRecord::create([
                    "report_id" => $metadata?->report_id ?? "",
                    "source_ip" => $record?->row?->source_ip ?? "",
                    "count" => (int) ($record?->row?->count ?? 0),
                    "header_from" => $record?->identifiers?->header_from ?? "",
                    "envelope_from" =>
                        $record?->identifiers?->envelope_from ?? null,
                    "envelope_to" =>
                        $record?->identifiers?->envelope_to ?? null,
                    "disposition" =>
                        $record?->row?->policy_evaluated?->disposition ?? "",
                    "dkim" => $record?->row?->policy_evaluated?->dkim ?? "",
                    "spf" => $record?->row?->policy_evaluated?->spf ?? "",
                    "reason" =>
                        $record?->row?->policy_evaluated?->reason?->comment ??
                        null,
                    "dkim_domain" =>
                        $record?->auth_results?->dkim?->domain ?? null,
                    "dkim_selector" =>
                        $record?->auth_results?->dkim?->selector ?? null,
                    "dkim_result" =>
                        $record?->auth_results?->dkim?->result ?? null,
                    "spf_domain" =>
                        $record?->auth_results?->spf?->domain ?? null,
                    "spf_result" =>
                        $record?->auth_results?->spf?->result ?? null,
                ]);
            }
        }

        return $reports;
    }

    private function indexDmarcReports(array $reports): void
    {
        if ($this->elasticClient instanceof ElasticClient && !empty($reports)) {
            $params = [
                "index" => self::INDEX_NAME,
                "body" => [],
            ];
            foreach ($reports as $report) {
                foreach ($report->records as $record) {
                    $metadata = $report->report_metadata;
                    $policy = $report->policy_published;
                    $begin = Carbon::createFromTimestampUTC(
                        (int) $metadata?->date_range?->begin,
                    );
                    $end = Carbon::createFromTimestampUTC(
                        (int) $metadata?->date_range?->end,
                    );

                    $params["body"][] = [
                        "report_id" => $metadata?->report_id ?? "",
                        "org_name" => $metadata?->org_name ?? "",
                        "org_email" => $metadata?->email ?? "",
                        "org_extra_contact_info" =>
                            $metadata?->xtra_contact_info ?? "",
                        "date_range" => [
                            "date_begin" => $begin,
                            "date_end" => $end,
                        ],
                        "date_begin" => $begin,
                        "date_end" => $end,
                        "published_policy" => [
                            "domain" => $policy?->domain ?? "",
                            "adkim" => $policy?->adkim ?? "",
                            "aspf" => $policy?->aspf ?? "",
                            "p" => $policy?->p ?? "",
                            "sp" => $policy?->sp ?? "",
                            "pct" => (int) ($policy?->pct ?? 0),
                            "fo" => (int) ($policy?->fo ?? 0),
                        ],
                        "source_ip" => $record?->row?->source_ip ?? "",
                        "count" => (int) ($record?->row?->count ?? 0),
                        "header_from" =>
                            $record?->identifiers?->header_from ?? "",
                        "envelope_from" =>
                            $record?->identifiers?->envelope_from ?? "",
                        "envelope_to" =>
                            $record?->identifiers?->envelope_to ?? "",
                        "disposition" =>
                            $record?->row?->policy_evaluated?->disposition ??
                            "",
                        "dkim" => $record?->row?->policy_evaluated?->dkim ?? "",
                        "spf" => $record?->row?->policy_evaluated?->spf ?? "",
                        "reason" =>
                            $record?->row?->policy_evaluated?->reason
                                ?->comment ?? "",
                        "dkim_domain" =>
                            $record?->auth_results?->dkim?->domain ?? "",
                        "dkim_selector" =>
                            $record?->auth_results?->dkim?->selector ?? "",
                        "dkim_result" =>
                            $record?->auth_results?->dkim?->result ?? "",
                        "spf_domain" =>
                            $record?->auth_results?->spf?->domain ?? "",
                        "spf_result" =>
                            $record?->auth_results?->spf?->result ?? "",
                    ];
                }
            }
            $this->elasticClient->bulk($params);
        }
    }
}
