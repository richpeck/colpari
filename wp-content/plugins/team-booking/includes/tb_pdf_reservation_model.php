<?php

defined('ABSPATH') or die('No script kiddies please!');
require TEAMBOOKING_PATH . '/libs/fpdf/fpdf.php';

use TeamBooking\Functions,
    TeamBooking\Database\Services;

/** @var TeamBooking_ReservationData $reservation */
class PDF_Report extends FPDF
{
    /** @var TeamBooking_ReservationData $reservation */
    public $reservation;

    function Header()
    {
        $text = sprintf(__('Reservation %s', 'team-booking'), '#' . $this->reservation->getDatabaseId(TRUE));
        $this->SetFont('Arial', 'B', 20);
        $this->SetFillColor(128, 178, 234);
        $this->SetTextColor(255);
        $this->Cell(0, 30, $text, 0, 1, 'C', TRUE);
    }

    function Footer()
    {
        $text = sprintf(__('Page %s', 'team-booking'), $this->PageNo() . '/{nb}');
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0);
        $this->SetXY($this->lMargin, -60);
        $this->Cell(0, 20, $text, 'T', 0, 'C');
    }

    function SetMargin($margin)
    {
        $this->SetTopMargin($margin);
        $this->SetLeftMargin($margin);
        $this->SetRightMargin($margin);
        $this->SetAutoPageBreak(TRUE, $margin);
    }

    function SetCellMargin($margin)
    {
        $this->cMargin = $margin;
    }

    // Create Table
    function WriteTable($tcolums)
    {
        // go through all colums
        for ($i = 0; $i < sizeof($tcolums); $i++) {
            $current_col = $tcolums[ $i ];
            $height = 0;

            // get max height of current col
            $nb = 0;
            for ($b = 0; $b < sizeof($current_col); $b++) {
                // set style
                $this->SetFont($current_col[ $b ]['font_name'], $current_col[ $b ]['font_style'], $current_col[ $b ]['font_size']);
                $color = explode(',', $current_col[ $b ]['fillcolor']);
                $this->SetFillColor($color[0], $color[1], $color[2]);
                $color = explode(',', $current_col[ $b ]['textcolor']);
                $this->SetTextColor($color[0], $color[1], $color[2]);
                $color = explode(',', $current_col[ $b ]['drawcolor']);
                $this->SetDrawColor($color[0], $color[1], $color[2]);
                $this->SetLineWidth($current_col[ $b ]['linewidth']);

                $nb = max($nb, $this->NbLines($current_col[ $b ]['width'], $current_col[ $b ]['text']));
                $height = $current_col[ $b ]['height'];
            }
            $h = $height * $nb;


            // Issue a page break first if needed
            $this->CheckPageBreak($h);

            // Draw the cells of the row
            for ($b = 0; $b < sizeof($current_col); $b++) {
                $w = $current_col[ $b ]['width'];
                $a = $current_col[ $b ]['align'];

                // Save the current position
                $x = $this->GetX();
                $y = $this->GetY();

                // set style
                $this->SetFont($current_col[ $b ]['font_name'], $current_col[ $b ]['font_style'], $current_col[ $b ]['font_size']);
                $color = explode(',', $current_col[ $b ]['fillcolor']);
                $this->SetFillColor($color[0], $color[1], $color[2]);
                $color = explode(',', $current_col[ $b ]['textcolor']);
                $this->SetTextColor($color[0], $color[1], $color[2]);
                $color = explode(',', $current_col[ $b ]['drawcolor']);
                $this->SetDrawColor($color[0], $color[1], $color[2]);
                $this->SetLineWidth($current_col[ $b ]['linewidth']);

                $color = explode(',', $current_col[ $b ]['fillcolor']);
                $this->SetDrawColor($color[0], $color[1], $color[2]);


                // Draw Cell Background
                $this->Rect($x, $y, $w, $h, 'FD');

                $color = explode(',', $current_col[ $b ]['drawcolor']);
                $this->SetDrawColor($color[0], $color[1], $color[2]);

                // Draw Cell Border
                if (substr_count($current_col[ $b ]['linearea'], 'T') > 0) {
                    $this->Line($x, $y, $x + $w, $y);
                }

                if (substr_count($current_col[ $b ]['linearea'], 'B') > 0) {
                    $this->Line($x, $y + $h, $x + $w, $y + $h);
                }

                if (substr_count($current_col[ $b ]['linearea'], 'L') > 0) {
                    $this->Line($x, $y, $x, $y + $h);
                }

                if (substr_count($current_col[ $b ]['linearea'], 'R') > 0) {
                    $this->Line($x + $w, $y, $x + $w, $y + $h);
                }


                // Print the text
                $this->MultiCell($w, $current_col[ $b ]['height'], $current_col[ $b ]['text'], 0, $a, 0);

                // Put the position to the right of the cell
                $this->SetXY($x + $w, $y);
            }

            // Go to the next line
            $this->Ln($h);
        }
    }


    // If the height h would cause an overflow, add a new page immediately
    function CheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }


    // Computes the number of lines a MultiCell of width w will take
    function NbLines($w, $txt)
    {
        $cw =& $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[ $nb - 1 ] === "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[ $i ];
            if ($c === "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c === ' ')
                $sep = $i;
            $l += $cw[ $c ];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }

        return $nl;
    }
}

// Letter: 612x792pt, A4: 595x842pt
$pdf = new PDF_Report('P', 'pt', $size = 'Letter');

$pdf->reservation = $reservation;

$pdf->AliasNbPages();

$pdf->SetMargin(40);

$bullet = chr(149);

$pdf->AddPage();
$pdf->SetFont('Arial', '', 18);

$pdf->SetFillColor(128, 178, 234);
$pdf->SetTextColor(255);
$pdf->MultiCell(0, 30, $reservation->getServiceName(TRUE), 0, 'C', TRUE);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetY(120);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 13, __('Customer', 'team-booking'));
$pdf->Cell(100, 13, $reservation->getCustomerDisplayName());

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 13, __('Date', 'team-booking'));
$pdf->SetFont('Arial', '');
if (NULL === $reservation->getStart()) {
    $date = esc_html__('Unscheduled', 'team-booking');
} elseif ($reservation->isAllDay()) {
    $date = Functions\dateFormatter($reservation->getStart(), TRUE)->date
        . '(' . esc_html__('All day', 'team-booking') . ')';
} else {
    $date = Functions\dateFormatter($reservation->getStart())->date
        . ' ' . Functions\dateFormatter($reservation->getStart())->time
        . ' - ' . Functions\dateFormatter($reservation->getEnd())->time;
}
$pdf->Cell(
    100,
    13,
    $date,
    0,
    1
);

$pdf->Ln(50);

$pdf->Write(20, __("Customer's data", 'team-booking'));

$pdf->SetCellMargin(10);

foreach ($reservation->getFieldsArray() as $key => $value) {
    try {
        $label_from_hook = \TeamBooking\Database\Forms::getTitleFromHook(Services::get($reservation->getServiceId())->getForm(), $key);
    } catch (Exception $ex) {
        $label_from_hook = FALSE;
    }
    if ($label_from_hook) {
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->SetX(180);
        $pdf->SetTextColor(192, 192, 192);
        $pdf->Cell(0, 20, $label_from_hook, 'L', 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetX(180);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 20, $value, 'L', 1);
    } else {
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->SetX(180);
        $pdf->SetTextColor(192, 192, 192);
        $pdf->Cell(0, 20, $key, 'L', 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetX(180);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 20, $value, 'L', 1);
    }
}

$pdf->SetCellMargin(0);

$pdf->Ln(20);
$pdf->Write(20, __('Reservation data', 'team-booking'));

$pdf->SetCellMargin(10);

$pdf->SetFont('Arial', 'I', 12);
$pdf->SetX(180);
$pdf->SetTextColor(192, 192, 192);
$pdf->Cell(0, 20, 'Coworker', 'L', 1);
$pdf->SetFont('Arial', '', 12);
$pdf->SetX(180);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 20, Functions\getSettings()->getCoworkerData($reservation->getCoworker())->getDisplayName(), 'L', 1);

if ($reservation->getPrice() > 0) {
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->SetX(180);
    $pdf->SetTextColor(192, 192, 192);
    $pdf->Cell(0, 20, __('Price', 'team-booking'), 'L', 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetX(180);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 20, Functions\currencyCodeToSymbol($reservation->getPrice(), $reservation->getCurrencyCode(), TRUE), 'L', 1);
}

if ($reservation->getServiceClass() === 'event') {
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->SetX(180);
    $pdf->SetTextColor(192, 192, 192);
    $pdf->Cell(0, 20, __('Tickets', 'team-booking'), 'L', 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetX(180);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 20, $reservation->getTickets(), 'L', 1);
}


$pdf->Ln(50);

$pdf->SetFont('Arial', 'U', 12);
$pdf->SetTextColor(1, 162, 232);

$pdf->Output();