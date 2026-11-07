<?php
require_once __DIR__ . '/SimplePdf.php';

class CvPdfRenderer
{
    private SimplePdf $pdf;
    private array $cv;
    private string $root;

    public function __construct(array $cv, ?string $projectRoot = null)
    {
        $this->cv = $cv;
        $this->root = $projectRoot ?: dirname(__DIR__, 2);
        $this->pdf = new SimplePdf();
    }

    public function render(): string
    {
        $id = max(1, min(6, (int)($this->cv['template_id'] ?? 1)));
        $method = 'template' . $id;
        $this->{$method}();
        return $this->pdf->output();
    }

    private function s(string $key, string $default = ''): string
    {
        return trim((string)($this->cv[$key] ?? $default));
    }

    private function photoPath(): ?string
    {
        $photo = $this->s('photo');
        if ($photo === '') return null;
        if (preg_match('#^https?://#i', $photo)) return null;
        $path = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($photo, '/\\'));
        $real = realpath($path);
        if (!$real || !is_file($real)) return null;
        $root = realpath($this->root);
        if (!$root || strncmp($real, rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, strlen(rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) !== 0) return null;
        return $real;
    }

    private function period(array $row, string $start = 'start_date', string $end = 'end_date'): string
    {
        $a = trim((string)($row[$start] ?? ''));
        $b = trim((string)($row[$end] ?? ''));
        if ($a === '' && $b === '') return '';
        return $a . ($b !== '' ? ' - ' . $b : ' - Présent');
    }

    private function sectionTitle(float $x, float $y, string $title, string $color, float $w = 230): float
    {
        $this->pdf->text($x,$y,strtoupper($title),9,'bold',$color);
        $this->pdf->line($x,$y+14,$x+$w,$y+14,$color,0.8);
        return $y+24;
    }

    private function drawPhoto(float $x, float $y, float $w, float $h, string $placeholderBg = '#D9D9D9', string $placeholderFg = '#333333'): void
    {
        $path = $this->photoPath();
        if ($path && $this->pdf->image($path,$x,$y,$w,$h)) {
            $this->pdf->strokeRect($x,$y,$w,$h,'#FFFFFF',1);
            return;
        }
        $this->pdf->fillRect($x,$y,$w,$h,$placeholderBg);
        $name = $this->s('full_name','CV');
        $initial = strtoupper(substr($name,0,1));
        $this->pdf->textCentered($x+$w/2,$y+$h/2-18,$initial,34,'bold',$placeholderFg);
    }

    private function drawContactList(float $x, float $y, float $w, string $color, string $muted, float $size = 9): float
    {
        $items = [
            ['Email', $this->s('email')],
            ['Téléphone', $this->s('phone')],
            ['Adresse', $this->s('address')],
        ];
        if ($this->s('age') !== '') $items[] = ['Âge', $this->s('age') . ' ans'];
        foreach ($items as [$label,$value]) {
            if ($value === '') continue;
            $this->pdf->text($x,$y,$label,7,'bold',$muted);
            $y += 10;
            $y = $this->pdf->paragraph($x,$y,$w,$value,$size,'regular',$color,$size+3,3);
            $y += 5;
        }
        return $y;
    }

    private function drawSkills(float $x, float $y, float $w, string $color, string $muted, float $size = 9): float
    {
        foreach (($this->cv['skills'] ?? []) as $skill) {
            $name = trim((string)($skill['skill_name'] ?? ''));
            if ($name === '') continue;
            $this->pdf->text($x,$y,'• ' . $name,$size,'bold',$color);
            $level = trim((string)($skill['level'] ?? ''));
            if ($level !== '') $this->pdf->textRight($x+$w,$y,$level,7,'regular',$muted);
            $y += $size+6;
        }
        return $y;
    }

    private function drawLanguages(float $x, float $y, float $w, string $color, string $muted, float $size = 9): float
    {
        foreach (($this->cv['languages'] ?? []) as $lang) {
            $name = trim((string)($lang['language_name'] ?? ''));
            if ($name === '') continue;
            $this->pdf->text($x,$y,$name,$size,'bold',$color);
            $level = trim((string)($lang['level'] ?? ''));
            if ($level !== '') $this->pdf->textRight($x+$w,$y,$level,7,'regular',$muted);
            $y += $size+7;
        }
        return $y;
    }

    private function drawExperiences(float $x, float $y, float $w, string $accent, string $text, string $muted, float $titleSize = 10.5): float
    {
        foreach (($this->cv['experiences'] ?? []) as $exp) {
            if ($y > 744) break;
            $job = trim((string)($exp['job_title'] ?? ''));
            $company = trim((string)($exp['company'] ?? ''));
            $period = $this->period($exp);
            if ($period !== '') $this->pdf->textRight($x+$w,$y,$period,7.2,'regular',$muted);
            $this->pdf->text($x,$y,$job !== '' ? $job : 'Expérience',$titleSize,'bold',$text);
            $y += 15;
            if ($company !== '') { $this->pdf->text($x,$y,$company,8.5,'bold',$accent); $y += 13; }
            $desc = trim((string)($exp['description'] ?? ''));
            if ($desc !== '') $y = $this->pdf->paragraph($x,$y,$w,$desc,8.2,'regular',$text,11.5,4);
            $y += 9;
            $this->pdf->line($x,$y,$x+$w,$y,'#D9D9D9',0.5);
            $y += 10;
        }
        return $y;
    }

    private function drawEducation(float $x, float $y, float $w, string $accent, string $text, string $muted): float
    {
        foreach (($this->cv['education'] ?? []) as $edu) {
            if ($y > 765) break;
            $degree = trim((string)($edu['degree'] ?? ''));
            $school = trim((string)($edu['school'] ?? ''));
            $period = $this->period($edu,'start_year','end_year');
            $this->pdf->text($x,$y,$degree !== '' ? $degree : 'Formation',9.5,'bold',$text);
            if ($period !== '') $this->pdf->textRight($x+$w,$y,$period,7.2,'regular',$muted);
            $y += 14;
            if ($school !== '') $this->pdf->text($x,$y,$school,8.2,'regular',$accent);
            $y += 18;
        }
        return $y;
    }

    // Template 1 — editorial / navy + warm beige
    private function template1(): void
    {
        $p=$this->pdf; $p->addPage();
        $bg='#F7F3EA'; $navy='#13263D'; $rust='#8E5D3F'; $text='#26313A'; $muted='#6F6B63';
        $p->fillRect(0,0,595.28,841.89,$bg); $p->fillRect(0,0,11,841.89,$navy);
        $p->text(48,35,'CURRICULUM VITAE',8,'bold',$rust);
        $p->text(48,61,$this->s('full_name','Votre nom'),31,'serif',$navy);
        if($this->s('title')!=='') $p->text(48,102,strtoupper($this->s('title')),9,'bold',$rust);
        $this->drawPhoto(442,35,105,122,'#D9D0BF',$navy);
        $p->line(38,177,557,177,'#CFC7B7',0.8);
        $p->fillRect(11,178,176,663,'#EFE9DE');
        $y=207; $y=$this->sectionTitle(34,$y,'Contact',$navy,125); $y=$this->drawContactList(34,$y,125,$text,$rust,8.5);
        $y+=9; $y=$this->sectionTitle(34,$y,'Expertise',$navy,125); $y=$this->drawSkills(34,$y,125,$text,$muted,8.3);
        $y+=10; $y=$this->sectionTitle(34,$y,'Langues',$navy,125); $this->drawLanguages(34,$y,125,$text,$muted,8.3);
        $x=214; $w=333; $y=207;
        if($this->s('about_me')!==''){ $p->text($x,$y,'01',20,'serif','#C9B9A5'); $p->text($x+46,$y+2,'PROFIL',9,'bold',$navy); $y=$p->paragraph($x+46,$y+19,$w-46,$this->s('about_me'),9,'serif',$text,13,6); $y+=12; }
        $p->text($x,$y,'02',20,'serif','#C9B9A5'); $p->text($x+46,$y+2,'EXPÉRIENCE',9,'bold',$navy); $y+=30; $y=$this->drawExperiences($x+46,$y,$w-46,$rust,$text,$muted,10);
        if($y<700){ $y+=2; $p->text($x,$y,'03',20,'serif','#C9B9A5'); $p->text($x+46,$y+2,'FORMATION',9,'bold',$navy); $y+=29; $this->drawEducation($x+46,$y,$w-46,$rust,$text,$muted); }
    }

    // Template 2 — dark tech / gold
    private function template2(): void
    {
        $p=$this->pdf; $p->addPage(); $dark='#17191E'; $gold='#D7B15B'; $paper='#F7F7F5'; $text='#1B1D22'; $muted='#777A81';
        $p->fillRect(0,0,595.28,841.89,$paper); $p->fillRect(0,0,196,841.89,$dark);
        $p->text(28,28,'CV',15,'bold','#FFFFFF'); $p->text(51,28,'•',14,'bold',$gold);
        $this->drawPhoto(39,76,118,146,'#2C2F36','#FFFFFF');
        $y=252; $p->text(28,$y,'CONTACT',8,'bold',$gold); $p->line(28,$y+14,166,$y+14,'#454851',0.7); $y+=26; $y=$this->drawContactList(28,$y,138,'#F1F1EE','#A8ABB2',8.2);
        $y+=6; $p->text(28,$y,'COMPÉTENCES',8,'bold',$gold); $p->line(28,$y+14,166,$y+14,'#454851',0.7); $y+=26; $y=$this->drawSkills(28,$y,138,'#F1F1EE','#A8ABB2',8.2);
        $y+=7; $p->text(28,$y,'LANGUES',8,'bold',$gold); $p->line(28,$y+14,166,$y+14,'#454851',0.7); $y+=26; $this->drawLanguages(28,$y,138,'#F1F1EE','#A8ABB2',8.2);
        $x=229;$w=322;$y=57; $p->text($x,$y,$this->s('full_name','Votre nom'),29,'bold',$text); $y+=39; if($this->s('title')!==''){$p->text($x,$y,strtoupper($this->s('title')),8.5,'bold',$gold);$y+=30;}
        $p->line($x,$y,$x+$w,$y,$gold,2);$y+=24;
        if($this->s('about_me')!==''){ $p->text($x,$y,'PROFILE / 01',8,'bold',$gold); $y+=19; $y=$p->paragraph($x,$y,$w,$this->s('about_me'),9,'regular',$text,13,6); $y+=18; }
        $p->text($x,$y,'EXPERIENCE / 02',8,'bold',$gold);$y+=24;$y=$this->drawExperiences($x,$y,$w,$gold,$text,$muted,10.2);
        if($y<700){$p->text($x,$y,'EDUCATION / 03',8,'bold',$gold);$y+=23;$this->drawEducation($x,$y,$w,$gold,$text,$muted);}
    }

    // Template 3 — Swiss minimal / black + red
    private function template3(): void
    {
        $p=$this->pdf; $p->addPage(); $red='#E32323'; $black='#0B0B0B'; $muted='#666666';
        $p->fillRect(0,0,595.28,841.89,'#FFFFFF');
        $p->text(31,39,'03',9,'bold',$red); $p->text(77,32,'CURRICULUM VITAE',8,'regular',$black);
        $p->text(77,55,$this->s('full_name','Votre nom'),32,'bold',$black); if($this->s('title')!=='')$p->text(77,99,$this->s('title'),9,'regular',$muted);
        $this->drawPhoto(492,31,67,78,'#EEEEEE',$black); $p->fillRect(30,136,529,6,$black);
        $x=31;$y=171;$left=162;$gap=28;$rightX=$x+$left+$gap;$rightW=529-$left-$gap;
        $p->text($x,$y,'CONTACT',8,'bold',$red);$y+=20;$y=$this->drawContactList($x,$y,$left,$black,$muted,8.4);$y+=7;$p->text($x,$y,'SKILLS',8,'bold',$red);$y+=20;$y=$this->drawSkills($x,$y,$left,$black,$muted,8.2);$y+=10;$p->text($x,$y,'LANGUAGES',8,'bold',$red);$y+=20;$this->drawLanguages($x,$y,$left,$black,$muted,8.2);
        $y=171;if($this->s('about_me')!==''){ $p->text($rightX,$y,'01  PROFILE',8,'bold',$red);$y+=21;$y=$p->paragraph($rightX,$y,$rightW,$this->s('about_me'),9,'regular',$black,13,6);$y+=15; }
        $p->text($rightX,$y,'02  EXPERIENCE',8,'bold',$red);$y+=22;$y=$this->drawExperiences($rightX,$y,$rightW,$red,$black,$muted,10);
        if($y<720){$p->text($rightX,$y,'03  EDUCATION',8,'bold',$red);$y+=22;$this->drawEducation($rightX,$y,$rightW,$red,$black,$muted);}
        $p->fillRect(30,817,529,2,$black); $p->textRight(559,823,'CV / ' . date('Y'),7,'regular',$muted);
    }
    private function template4(): void
    {
        $p=$this->pdf; $p->addPage(); $purple='#5D3B8C';$coral='#EF806B';$cream='#FFF9F5';$text='#25213A';$muted='#756D82';
        $p->fillRect(0,0,595.28,841.89,$cream); $p->circle(544,23,96,$purple); $p->circle(10,196,58,$coral);
        $this->drawPhoto(42,41,104,104,'#F0D9D3',$purple);$p->strokeRect(38,37,112,112,$purple,2);
        $p->text(181,52,$this->s('full_name','Votre nom'),29,'bold',$purple);if($this->s('title')!=='')$p->text(182,91,$this->s('title'),10,'bold',$coral);
        $p->text(182,119,'CV • CREATIVE EDITION',7.5,'bold',$muted);$p->line(36,173,558,173,'#E8DCEB',1);
        $x=40;$side=163;$mainX=232;$mainW=320;$y=204;
        $p->text($x,$y,'HELLO.',20,'bold',$coral);$y+=34;if($this->s('about_me')!==''){$y=$p->paragraph($x,$y,$side,$this->s('about_me'),8.6,'regular',$text,12,9);$y+=18;}
        $p->text($x,$y,'CONTACT',8,'bold',$purple);$y+=20;$y=$this->drawContactList($x,$y,$side,$text,$muted,8.1);$y+=8;$p->text($x,$y,'SKILLS',8,'bold',$purple);$y+=20;$y=$this->drawSkills($x,$y,$side,$text,$muted,8.1);$y+=8;$p->text($x,$y,'LANGUAGES',8,'bold',$purple);$y+=20;$this->drawLanguages($x,$y,$side,$text,$muted,8.1);
        $y=204;$p->fillRect($mainX,$y,7,23,$coral);$p->text($mainX+18,$y+1,'EXPERIENCE',13,'bold',$purple);$y+=38;$y=$this->drawExperiences($mainX,$y,$mainW,$coral,$text,$muted,10.1);
        if($y<700){$p->fillRect($mainX,$y,7,23,$purple);$p->text($mainX+18,$y+1,'EDUCATION',13,'bold',$coral);$y+=38;$this->drawEducation($mainX,$y,$mainW,$purple,$text,$muted);}
    }
    private function template5(): void
    {
        $p=$this->pdf;$p->addPage();$teal='#0F6658';$dark='#20302E';$muted='#667572';$p->fillRect(0,0,595.28,841.89,'#FFFFFF');$p->fillRect(0,0,595.28,13,$teal);
        $p->text(39,48,'EXECUTIVE PROFILE',7.5,'bold',$teal);$p->text(39,70,$this->s('full_name','Votre nom'),27,'bold',$dark);if($this->s('title')!=='')$p->text(39,106,$this->s('title'),9.5,'regular',$muted);
        $this->drawPhoto(468,35,84,96,'#DCE7E4',$teal);$p->line(39,151,555,151,'#D8E0DE',0.8);
        $left=166;$gap=30;$mainX=39+$left+$gap;$mainW=555-$mainX;$y=181;
        $p->text(39,$y,'DETAILS',8,'bold',$teal);$y+=21;$y=$this->drawContactList(39,$y,$left,$dark,$muted,8.3);$y+=9;$p->text(39,$y,'CORE SKILLS',8,'bold',$teal);$y+=21;$y=$this->drawSkills(39,$y,$left,$dark,$muted,8.2);$y+=10;$p->text(39,$y,'LANGUAGES',8,'bold',$teal);$y+=21;$this->drawLanguages(39,$y,$left,$dark,$muted,8.2);
        $y=181;if($this->s('about_me')!==''){ $p->text($mainX,$y,'PROFILE',8,'bold',$teal);$y+=21;$y=$p->paragraph($mainX,$y,$mainW,$this->s('about_me'),9,'regular',$dark,13,7);$y+=17; }
        $p->text($mainX,$y,'PROFESSIONAL EXPERIENCE',8,'bold',$teal);$y+=23;$y=$this->drawExperiences($mainX,$y,$mainW,$teal,$dark,$muted,10.1);
        if($y<710){$p->text($mainX,$y,'EDUCATION',8,'bold',$teal);$y+=23;$this->drawEducation($mainX,$y,$mainW,$teal,$dark,$muted);}
        $p->fillRect(0,825,595.28,16,'#EFF5F3');$p->textCentered(297.64,829,'CURRICULUM VITAE',6.5,'bold',$teal);
    }
    private function template6(): void
    {
        $p=$this->pdf;$p->addPage();$cream='#FBF7EF';$brown='#A36A45';$text='#3A332C';$muted='#7E7166';$p->fillRect(0,0,595.28,841.89,$cream);
        $p->textCentered(297.64,25,'—  •  —',10,'serif',$brown);$this->drawPhoto(263,48,70,70,'#DED2C4',$brown);$p->strokeRect(259,44,78,78,'#9F8E7E',0.8);
        $p->textCentered(297.64,134,'CURRICULUM VITAE',7.5,'bold',$brown);$p->textCentered(297.64,154,$this->s('full_name','Votre nom'),27,'serif-bold',$text);if($this->s('title')!=='')$p->textCentered(297.64,190,$this->s('title'),9,'regular',$muted);$p->line(63,218,532,218,'#CFC3B4',0.8);
        $leftX=63;$leftW=152;$mainX=250;$mainW=282;$y=247;
        $p->text($leftX,$y,'COORDONNÉES',8,'bold',$brown);$y+=20;$y=$this->drawContactList($leftX,$y,$leftW,$text,$muted,8.2);$y+=8;$p->text($leftX,$y,'COMPÉTENCES',8,'bold',$brown);$y+=20;$y=$this->drawSkills($leftX,$y,$leftW,$text,$muted,8.1);$y+=9;$p->text($leftX,$y,'LANGUES',8,'bold',$brown);$y+=20;$this->drawLanguages($leftX,$y,$leftW,$text,$muted,8.1);
        $p->line(230,240,230,790,'#D8CEC3',0.7);$y=247;if($this->s('about_me')!==''){ $p->text($mainX,$y,'PROFIL',8,'bold',$brown);$y+=20;$y=$p->paragraph($mainX,$y,$mainW,$this->s('about_me'),9,'serif',$text,13.2,7);$y+=16; }
        $p->text($mainX,$y,'EXPÉRIENCE',8,'bold',$brown);$y+=22;$y=$this->drawExperiences($mainX,$y,$mainW,$brown,$text,$muted,10);
        if($y<700){$p->text($mainX,$y,'FORMATION',8,'bold',$brown);$y+=22;$this->drawEducation($mainX,$y,$mainW,$brown,$text,$muted);}
        $p->line(63,806,532,806,'#CFC3B4',0.8);$p->textCentered(297.64,816,'•  Curriculum Vitae  •',7,'serif',$muted);
    }
}
