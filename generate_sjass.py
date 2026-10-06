#!/usr/bin/env python3
"""
SJASS Student Roster & Report Card Generator
Shalaka Jatani Ali Secondary School – Oromia / Borana / Yabello
Academic Year 2018 EC / 2025-26 GC
"""
import openpyxl
from openpyxl.styles import PatternFill, Font, Alignment, Border, Side
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.datavalidation import DataValidation
from openpyxl.formatting.rule import ColorScaleRule, FormulaRule
from openpyxl.worksheet.page import PageMargins

# ─────────────────────────────────────────────────────────────
# CONSTANTS
# ─────────────────────────────────────────────────────────────
SCHOOL_EN   = "Shalaka Jatani Ali Secondary School"
SCHOOL_OM   = "Mana Barumsaa Sadarkaa 2ffaa Shalaka Jatani Ali"
SCHOOL_AM   = "ሳላካ ጅዓኒ አሊ ሁለተኛ ደረጃ ትምሕርት ቀት"
REGION      = "Oromia Regional State"
ZONE        = "Borana Zone"
WOREDA      = "Yabello"
PRINCIPAL   = "Ato [Principal Name]"
AY_EC       = "2018"
AY_GC       = "2025/26"
PASS_MARK   = 50
N_STU       = 40        # student slots per roster

DATA_START  = 5         # first data row (rows 1-4 = headers)

# ─── Colour palette ───────────────────────────────────────────
GD = "1A5E20"   # deep green – main headers
GM = "2E7D32"   # mid green  – sub-headers
GL = "E8F5E9"   # pale green – alternate rows
GO = "F9A825"   # gold       – avg / summary col
GL2= "FFF8E1"   # pale gold  – AV rows
BI = "DBEAFE"   # blue       – input cells (Sem I/II scores)
WH = "FFFFFF"
GY = "F5F5F5"
GB = "BDBDBD"   # gray border

# ─── Fills ────────────────────────────────────────────────────
fHdr  = PatternFill("solid", fgColor=GD)
fSub  = PatternFill("solid", fgColor=GM)
fAlt  = PatternFill("solid", fgColor=GL)
fGold = PatternFill("solid", fgColor=GO)
fGoldL= PatternFill("solid", fgColor=GL2)
fIn   = PatternFill("solid", fgColor=BI)
fWht  = PatternFill("solid", fgColor=WH)
fGray = PatternFill("solid", fgColor=GY)

# ─── Fonts ────────────────────────────────────────────────────
fT  = Font(name="Calibri", bold=True, size=14, color=WH)
fSH = Font(name="Calibri", bold=True, size=11, color=WH)
fH  = Font(name="Calibri", bold=True, size=10, color=WH)
fH8 = Font(name="Calibri", bold=True, size=8,  color=WH)
fB  = Font(name="Calibri", size=10)
fBo = Font(name="Calibri", bold=True, size=10)
fS  = Font(name="Calibri", size=9)
fSB = Font(name="Calibri", bold=True, size=9)
fLk = Font(name="Calibri", size=10, color="1155CC", underline="single")

# ─── Alignments ───────────────────────────────────────────────
aC   = Alignment(horizontal="center", vertical="center", wrap_text=True)
aL   = Alignment(horizontal="left",   vertical="center", wrap_text=True)
aR   = Alignment(horizontal="right",  vertical="center")
aRot = Alignment(horizontal="center", vertical="bottom", text_rotation=90, wrap_text=True)

# ─── Borders ──────────────────────────────────────────────────
_t = Side(style="thin",   color=GB)
_m = Side(style="medium", color="757575")
_g = Side(style="medium", color=GD)
bT = Border(left=_t, right=_t, top=_t, bottom=_t)
bM = Border(left=_m, right=_m, top=_m, bottom=_m)

# ─── Subjects ─────────────────────────────────────────────────
# (Afan Oromo name, Amharic name, English name)
SUBJ_9_10 = [
    ("Afaan Oromoo",      "አፋን ኦሮሞ",      "Afan Oromo"),
    ("Amaariffaa",        "አማርኛ",                    "Amharic"),
    ("Ingiliizii",        "እንግሊዘኛ",        "English"),
    ("Herrega",           "ሂሳብ",                          "Math"),
    ("Fisikii",           "ፊዚክስ",                    "Physics"),
    ("Keemii",            "ኬሚስትሪ",              "Chemistry"),
    ("Bayoloojii",        "ባዮሎጂ",                    "Biology"),
    ("Joogirafii",        "ጂዮግራፊ",              "Geography"),
    ("Seenaa",            "ታሪክ",                          "History"),
    ("Lammummaa",         "ዜጋነት",                    "Citizenship"),
    ("TM / IT",           "TM / IT",              "IT"),
    ("HPE",               "ስፖርት",                    "HPE"),
]

SUBJ_11_12 = [
    ("Af.Or / Amaariffaa","Af.Or/አማርኛ",             "Afan Or./Amh."),
    ("Ingiliizii",        "እንግሊዘኛ",        "English"),
    ("Herrega",           "ሂሳብ",                          "Math"),
    ("Fisikii (NS)",      "ፊዚክስ",                    "Physics (NS)"),
    ("Keemii (NS)",       "ኬሚስትሪ",              "Chemistry (NS)"),
    ("Bayoloojii (NS)",   "ባዮሎጂ",                    "Biology (NS)"),
    ("Joogirafii (SS)",   "ጂዮግራፊ",              "Geography (SS)"),
    ("Seenaa (SS)",       "ታሪክ",                          "History (SS)"),
    ("Dinagdee",          "Dinagdee",             "Economics (SS)"),
    ("TM / IT",           "TM / IT",              "IT"),
]

# ─── Sample students ──────────────────────────────────────────
STUDENTS = {
    9:  [("Lemi",    "Godana Guyo",    "M",15,"A"),
         ("Fatuma",  "Boru Hassan",    "F",14,"A"),
         ("Diriba",  "Jira Wario",     "M",16,"B"),
         ("Birhane", "Guta Amante",    "F",15,"B"),
         ("Kalif",   "Dida Roba",      "M",15,"C")],
    10: [("Asefa",   "Bule Gosa",      "M",16,"A"),
         ("Caaltuu", "Banti Dida",     "F",16,"A"),
         ("Galgalo", "Wario Jatani",   "M",17,"B"),
         ("Naima",   "Hassan Roba",    "F",15,"B"),
         ("Tolera",  "Gurmessa Jira",  "M",16,"C")],
    11: [("Abduba",  "Golicha Tuke",   "M",17,"A","NS"),
         ("Hidda",   "Bule Gosa",      "F",17,"A","NS"),
         ("Boru",    "Halake Galma",   "M",18,"A","SS"),
         ("Shukri",  "Dido Wario",     "F",17,"A","SS"),
         ("Kumera",  "Jatani Guyo",    "M",17,"B","NS")],
    12: [("Wakjira", "Boru Dida",      "M",18,"A","NS"),
         ("Falmata", "Halake Jima",    "F",18,"A","NS"),
         ("Guyyo",   "Roba Gurmessa",  "M",19,"A","SS"),
         ("Iftu",    "Wario Dida",     "F",18,"A","SS"),
         ("Desta",   "Golicha Amante", "M",18,"B","NS")],
}

SCORES = {
    9:  [([75,68,72,80,65,70,78,74,69,82,88,76],[80,72,68,85,70,75,82,78,72,86,90,80]),
         ([72,70,74,78,68,72,76,70,74,80,84,72],[76,74,70,82,72,76,80,74,78,84,88,76]),
         ([68,65,70,75,62,68,72,68,65,78,80,70],[72,68,72,80,66,72,76,72,70,82,84,74]),
         ([80,75,78,82,74,78,84,78,76,88,90,82],[84,78,80,86,76,80,88,82,80,90,92,84]),
         ([65,62,68,72,58,64,68,64,62,74,78,68],[70,66,70,76,62,68,72,68,66,78,82,72])],
    10: [([72,70,74,78,68,72,76,70,74,80,84,72],[76,74,70,82,72,76,80,74,78,84,88,76]),
         ([78,75,80,84,72,76,82,76,78,86,88,80],[82,78,82,88,74,78,86,80,82,90,90,84]),
         ([65,62,68,72,58,64,68,64,62,74,78,68],[70,66,70,76,62,68,72,68,66,78,82,72]),
         ([82,78,84,88,76,80,86,80,82,90,92,84],[86,82,86,90,78,82,90,84,86,92,94,88]),
         ([70,68,72,76,64,68,74,70,68,78,82,72],[74,72,74,80,66,72,78,74,72,82,86,76])],
    11: [([78,72,82,75,70,80, 0, 0, 0,85],[82,76,86,80,74,84, 0, 0, 0,88]),
         ([74,68,78,72,66,76, 0, 0, 0,82],[78,72,82,76,70,80, 0, 0, 0,86]),
         ([80,74,84, 0, 0, 0,78,72,76,82],[84,78,88, 0, 0, 0,82,76,80,86]),
         ([76,70,80, 0, 0, 0,74,68,72,78],[80,74,84, 0, 0, 0,78,72,76,82]),
         ([82,76,86,80,74,84, 0, 0, 0,88],[86,80,90,84,78,88, 0, 0, 0,92])],
    12: [([82,76,88,80,74,84, 0, 0, 0,88],[86,80,90,84,78,88, 0, 0, 0,92]),
         ([78,72,84,76,70,80, 0, 0, 0,84],[82,76,88,80,74,84, 0, 0, 0,88]),
         ([84,78,90, 0, 0, 0,82,76,80,88],[88,82,92, 0, 0, 0,86,80,84,90]),
         ([80,74,86, 0, 0, 0,78,72,76,84],[84,78,90, 0, 0, 0,82,76,80,88]),
         ([86,80,92,84,78,88, 0, 0, 0,92],[90,84,94,88,82,92, 0, 0, 0,96])],
}

# ─────────────────────────────────────────────────────────────
# HELPERS
# ─────────────────────────────────────────────────────────────
def cl(n):
    return get_column_letter(n)

def sc(ws, row, col, val=None, font=None, fill=None, align=None, border=None, fmt=None):
    c = ws.cell(row=row, column=col)
    if val   is not None: c.value        = val
    if font  is not None: c.font         = font
    if fill  is not None: c.fill         = fill
    if align is not None: c.alignment    = align
    if border is not None: c.border      = border
    if fmt   is not None: c.number_format= fmt
    return c

def mg(ws, r1, c1, r2, c2, val=None, font=None, fill=None, align=None):
    ws.merge_cells(start_row=r1, start_column=c1, end_row=r2, end_column=c2)
    c = ws.cell(row=r1, column=c1)
    if val   is not None: c.value     = val
    if font  is not None: c.font      = font
    if fill  is not None: c.fill      = fill
    if align is not None: c.alignment = align
    for r in range(r1, r2+1):
        for cc in range(c1, c2+1):
            ws.cell(row=r, column=cc).border = bT
    return c

def print_landscape(ws):
    ws.page_setup.orientation = "landscape"
    ws.page_setup.paperSize   = 9       # A4
    ws.page_setup.fitToPage   = True
    ws.page_setup.fitToWidth  = 1
    ws.page_setup.fitToHeight = 0
    ws.page_margins = PageMargins(left=0.39, right=0.39, top=0.47, bottom=0.47,
                                  header=0.28, footer=0.28)

def print_portrait(ws):
    ws.page_setup.orientation = "portrait"
    ws.page_setup.paperSize   = 9
    ws.page_setup.fitToPage   = True
    ws.page_setup.fitToWidth  = 1
    ws.page_setup.fitToHeight = 0
    ws.page_margins = PageMargins(left=0.39, right=0.39, top=0.47, bottom=0.47,
                                  header=0.28, footer=0.28)

# ─────────────────────────────────────────────────────────────
# ROSTER BUILDER  (Ros9 / Ros10 / Ros11 / Ros12)
# ─────────────────────────────────────────────────────────────
def build_roster(wb, grade):
    ws = wb.create_sheet(f"Ros{grade}")
    is_senior = grade in (11, 12)
    subjects  = SUBJ_11_12 if is_senior else SUBJ_9_10
    nsub      = len(subjects)

    # ── Column layout ─────────────────────────────────────────
    # Grade 9/10:  A=No B=Name C=Sex D=Age E=Sec F=Sem  G..→=subjs  →Total →Avg →Rank →Result →Remark
    # Grade 11/12: A=No B=Name C=Sex D=Age E=Sec F=Str  G=Sem  H..→=subjs → ...
    COL_NO   = 1
    COL_NAME = 2
    COL_SEX  = 3
    COL_AGE  = 4
    COL_SEC  = 5
    if is_senior:
        COL_STR  = 6
        COL_SEM  = 7
        COL_SUBJ0= 8
    else:
        COL_STR  = None
        COL_SEM  = 6
        COL_SUBJ0= 7

    COL_SUBJ_LAST = COL_SUBJ0 + nsub - 1
    COL_TOT  = COL_SUBJ_LAST + 1
    COL_AVG  = COL_TOT + 1
    COL_RANK = COL_AVG + 1
    COL_RES  = COL_RANK + 1
    COL_RMK  = COL_RES + 1
    LAST_COL = COL_RMK

    sc_l  = cl(COL_SUBJ0)           # first subject col letter
    ec_l  = cl(COL_SUBJ_LAST)       # last subject col letter
    tot_l = cl(COL_TOT)
    avg_l = cl(COL_AVG)
    rnk_l = cl(COL_RANK)
    res_l = cl(COL_RES)

    last_av_row = DATA_START + (N_STU - 1) * 3 + 2

    # ── Row 1: school banner ──────────────────────────────────
    mg(ws,1,1,1,LAST_COL,
       val=f"★  {SCHOOL_EN}  ·  {SCHOOL_OM}  ·  {SCHOOL_AM}  ★",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 30

    # ── Row 2: grade / year info ──────────────────────────────
    ord_map = {9:"9th",10:"10th",11:"11th",12:"12th"}
    mg(ws,2,1,2,LAST_COL,
       val=(f"Kutaa/Grade {grade} · Bara Barnootaa/Academic Year: "
            f"{AY_EC} EC / {AY_GC} GC · {ZONE}, {REGION} "
            f"· Baay. Qabsiyamaa/Pass Mark = {PASS_MARK}%"),
       font=Font(name="Calibri", bold=True, size=10, color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[2].height = 22

    # ── Rows 3-4: column headers ──────────────────────────────
    def hcell(r1,c1,r2,c2,val,fl=fHdr,fnt=fH,al=aC):
        mg(ws,r1,c1,r2,c2,val=val,font=fnt,fill=fl,align=al)

    id_hdrs = ["Lak.\nNo","Maqaa Barataa / Student Name",
               "Saala\nSex","Umurii\nAge","Kutaa\nSection"]
    for i,h in enumerate(id_hdrs, 1):
        hcell(3,i,4,i,h)
    if is_senior:
        hcell(3,COL_STR,4,COL_STR,"Damee\nStream")
        hcell(3,COL_SEM,4,COL_SEM,"Sem.")
    else:
        hcell(3,COL_SEM,4,COL_SEM,"Sem.")

    hcell(3,COL_SUBJ0,3,COL_SUBJ_LAST,
          "ADEEMSA BARNOOTAA / SUBJECT SCORES",
          fnt=fH8)
    for i,subj in enumerate(subjects):
        col = COL_SUBJ0 + i
        c = ws.cell(row=4, column=col)
        c.value     = f"{subj[0]}\n{subj[1]}\n{subj[2]}"
        c.font      = Font(name="Calibri", bold=True, size=8, color=WH)
        c.fill      = fHdr
        c.alignment = aRot
        c.border    = bT

    hcell(3,COL_TOT, 4,COL_TOT,  "Waligala\nTotal")
    hcell(3,COL_AVG, 4,COL_AVG,  "Gidgala\nAverage",
          fl=fGold, fnt=Font(name="Calibri",bold=True,size=10,color="3E2600"))
    hcell(3,COL_RANK,4,COL_RANK, "Sadarkaa\nRank")
    hcell(3,COL_RES, 4,COL_RES,  "Bu'aa\nResult")
    hcell(3,COL_RMK, 4,COL_RMK,  "Yaada\nRemark")

    ws.row_dimensions[3].height = 34
    ws.row_dimensions[4].height = 72

    # ── Column widths ─────────────────────────────────────────
    ws.column_dimensions["A"].width = 5
    ws.column_dimensions["B"].width = 24
    ws.column_dimensions["C"].width = 6
    ws.column_dimensions["D"].width = 6
    ws.column_dimensions["E"].width = 8
    if is_senior:
        ws.column_dimensions["F"].width = 7
        ws.column_dimensions["G"].width = 6
    else:
        ws.column_dimensions["F"].width = 6
    for i in range(nsub):
        ws.column_dimensions[cl(COL_SUBJ0+i)].width = 7
    ws.column_dimensions[cl(COL_TOT)].width  = 9
    ws.column_dimensions[cl(COL_AVG)].width  = 9
    ws.column_dimensions[cl(COL_RANK)].width = 7
    ws.column_dimensions[cl(COL_RES)].width  = 18
    ws.column_dimensions[cl(COL_RMK)].width  = 18

    # ── Data rows ─────────────────────────────────────────────
    stu_list = STUDENTS.get(grade, [])
    sc_list  = SCORES.get(grade, [])

    for n in range(N_STU):
        r1 = DATA_START + n * 3        # Sem I
        r2 = r1 + 1                    # Sem II
        r3 = r1 + 2                    # Average
        alt_fill = fAlt if (n % 2 == 1) else fWht

        ws.row_dimensions[r1].height = 15
        ws.row_dimensions[r2].height = 15
        ws.row_dimensions[r3].height = 16

        # Identity (merged 3 rows)
        stu  = stu_list[n] if n < len(stu_list) else None
        scos = sc_list[n]  if n < len(sc_list)  else None

        mg(ws,r1,COL_NO,  r3,COL_NO,   val=n+1,
           font=fBo, fill=alt_fill, align=aC)
        mg(ws,r1,COL_NAME,r3,COL_NAME,
           val=(f"{stu[0]} {stu[1]}" if stu else ""),
           font=fB, fill=alt_fill, align=aL)
        mg(ws,r1,COL_SEX, r3,COL_SEX,
           val=(stu[2] if stu else ""),
           font=fB, fill=alt_fill, align=aC)
        mg(ws,r1,COL_AGE, r3,COL_AGE,
           val=(stu[3] if stu else ""),
           font=fB, fill=alt_fill, align=aC)
        mg(ws,r1,COL_SEC, r3,COL_SEC,
           val=(stu[4] if stu else ""),
           font=fB, fill=alt_fill, align=aC)
        if is_senior:
            mg(ws,r1,COL_STR,r3,COL_STR,
               val=(stu[5] if stu and len(stu)>5 else ""),
               font=fBo, fill=alt_fill, align=aC)

        # Sem label column
        for row, lbl in [(r1,"I"),(r2,"II"),(r3,"AV")]:
            is_av = lbl == "AV"
            sc(ws, row, COL_SEM, lbl,
               font=fSB if is_av else fS,
               fill=fGoldL if is_av else alt_fill,
               align=aC, border=bT)

        # Subject scores
        for si in range(nsub):
            col = COL_SUBJ0 + si
            clc = cl(col)

            # Sem I
            v1 = None
            if scos:
                raw = scos[0][si]
                v1  = raw if raw != 0 else None
            c = ws.cell(row=r1, column=col)
            if v1 is not None: c.value = v1
            c.font = fS; c.fill = fIn; c.alignment = aC
            c.border = bT; c.number_format = "0.0"

            # Sem II
            v2 = None
            if scos:
                raw = scos[1][si]
                v2  = raw if raw != 0 else None
            c = ws.cell(row=r2, column=col)
            if v2 is not None: c.value = v2
            c.font = fS; c.fill = fIn; c.alignment = aC
            c.border = bT; c.number_format = "0.0"

            # Average (formula)
            c = ws.cell(row=r3, column=col)
            c.value  = (f'=IF(COUNTA({clc}{r1}:{clc}{r2})=0,"",'
                        f'IFERROR(AVERAGE({clc}{r1}:{clc}{r2}),""))')
            c.font   = fSB; c.fill = fGoldL; c.alignment = aC
            c.border = bT;  c.number_format = "0.0"

        # Total column (all 3 rows)
        for row in (r1, r2):
            c = ws.cell(row=row, column=COL_TOT)
            c.value = (f'=IF(COUNTA({sc_l}{row}:{ec_l}{row})=0,"",'
                       f'SUM({sc_l}{row}:{ec_l}{row}))')
            c.font=fSB; c.fill=alt_fill; c.alignment=aC
            c.border=bT; c.number_format="0.0"

        c = ws.cell(row=r3, column=COL_TOT)
        c.value = (f'=IF(COUNTA({sc_l}{r3}:{ec_l}{r3})=0,"",'
                   f'SUM({sc_l}{r3}:{ec_l}{r3}))')
        c.font=fBo; c.fill=fGoldL; c.alignment=aC
        c.border=bT; c.number_format="0.0"

        # Average / Rank / Result – AV row only; blank in I/II rows
        for row in (r1, r2):
            for col in (COL_AVG, COL_RANK, COL_RES):
                ws.cell(row=row,column=col).fill   = alt_fill
                ws.cell(row=row,column=col).border = bT

        # Average
        c = ws.cell(row=r3, column=COL_AVG)
        c.value = (f'=IF({tot_l}{r3}="","",IFERROR('
                   f'{tot_l}{r3}/COUNTA({sc_l}{r3}:{ec_l}{r3}),""))')
        c.font = Font(name="Calibri",bold=True,size=10,color="3E2600")
        c.fill = fGold; c.alignment = aC; c.border = bT
        c.number_format = "0.00"

        # Rank
        c = ws.cell(row=r3, column=COL_RANK)
        c.value = (f'=IF({avg_l}{r3}="","",RANK({avg_l}{r3},'
                   f'{avg_l}${DATA_START+2}:{avg_l}${last_av_row},0))')
        c.font=fBo; c.fill=fGoldL; c.alignment=aC; c.border=bT

        # Result
        c = ws.cell(row=r3, column=COL_RES)
        c.value = (f'=IF({avg_l}{r3}="","",IF({avg_l}{r3}>={PASS_MARK},'
                   f'"Darbe / ያለፈ / Pass",'
                   f'"Kufe / ወድቃል / Fail"))')
        c.font=fBo; c.fill=fGoldL; c.alignment=aC; c.border=bT

        # Remark
        for row in (r1, r2, r3):
            c = ws.cell(row=row, column=COL_RMK)
            c.fill   = fGoldL if row==r3 else alt_fill
            c.border = bT; c.alignment = aL

        # Bottom divider after each student block
        for col in range(1, LAST_COL+1):
            existing = ws.cell(row=r3, column=col).border
            ws.cell(row=r3, column=col).border = Border(
                left=existing.left, right=existing.right,
                top=existing.top, bottom=Side(style="medium", color=GD))

    # ── Conditional formatting ────────────────────────────────
    res_range = f"{res_l}{DATA_START+2}:{res_l}{last_av_row}"
    ws.conditional_formatting.add(res_range,
        FormulaRule(
            formula=[f'ISNUMBER(SEARCH("Pass",{res_l}{DATA_START+2}))'],
            fill=PatternFill(bgColor="C8E6C9"),
            font=Font(color="1B5E20", bold=True)
        ))
    ws.conditional_formatting.add(res_range,
        FormulaRule(
            formula=[f'ISNUMBER(SEARCH("Fail",{res_l}{DATA_START+2}))'],
            fill=PatternFill(bgColor="FFCDD2"),
            font=Font(color="B71C1C", bold=True)
        ))
    ws.conditional_formatting.add(
        f"{avg_l}{DATA_START+2}:{avg_l}{last_av_row}",
        ColorScaleRule(start_type="num", start_value=0,  start_color="FF5252",
                       mid_type="num",   mid_value=50,   mid_color="FFEB3B",
                       end_type="num",   end_value=100,  end_color="4CAF50"))

    # ── Data validation ───────────────────────────────────────
    dv_sex = DataValidation(type="list", formula1='"M,F"',
                            allow_blank=True, showErrorMessage=False)
    ws.add_data_validation(dv_sex)
    if is_senior:
        dv_str = DataValidation(type="list", formula1='"NS,SS"',
                                allow_blank=True, showErrorMessage=False)
        ws.add_data_validation(dv_str)

    for n in range(N_STU):
        base = DATA_START + n * 3
        dv_sex.add(ws.cell(row=base, column=COL_SEX))
        if is_senior:
            dv_str.add(ws.cell(row=base, column=COL_STR))

    # ── Freeze panes & print ──────────────────────────────────
    ws.freeze_panes = ws.cell(row=DATA_START, column=COL_SUBJ0)
    ws.print_title_rows = "1:4"
    print_landscape(ws)

    return ws


# ─────────────────────────────────────────────────────────────
# REPORT CARD BUILDER  (Kard9 / Kard10 / Kard11 / Kard12)
# ─────────────────────────────────────────────────────────────
def build_kard(wb, grade):
    ws = wb.create_sheet(f"Kard{grade}")
    is_senior = grade in (11, 12)
    subjects  = SUBJ_11_12 if is_senior else SUBJ_9_10

    # ── Page / column setup ───────────────────────────────────
    # Columns: A=Subject(wide) | B-G=Sem I(5 parts+total) | H-M=Sem II | N=Remark
    # A=1, B=2..G=7 (6 cols sem1), H=8..M=13 (6 cols sem2), N=14
    PART_LABELS = ["Qo'ann.\nAssign","Qor.1\nTest 1","Qor.2\nTest 2",
                   "Walak.\nMid","Dhumaa\nFinal","Walig.\nTotal"]
    N_PARTS = 6       # 5 assessment + 1 total
    COL_SUBJ  = 1
    COL_S1_0  = 2     # Sem I Part 1
    COL_S1_TOT= 7     # Sem I Total
    COL_S2_0  = 8     # Sem II Part 1
    COL_S2_TOT= 13    # Sem II Total
    COL_RMK   = 14
    LAST_COL  = 14

    # Column widths
    ws.column_dimensions["A"].width = 22
    for c in range(COL_S1_0, LAST_COL+1):
        ws.column_dimensions[cl(c)].width = 8
    ws.column_dimensions[cl(COL_RMK)].width = 14

    # ── Row 1-2: School header ────────────────────────────────
    mg(ws,1,1,1,LAST_COL,
       val=SCHOOL_EN,
       font=Font(name="Calibri",bold=True,size=16,color=WH),
       fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 36

    mg(ws,2,1,2,LAST_COL,
       val=SCHOOL_OM,
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[2].height = 20

    mg(ws,3,1,3,LAST_COL,
       val=f"KAARDII GABASSAA BARATAA  /  STUDENT REPORT CARD  ·  Kutaa/Grade {grade}",
       font=Font(name="Calibri",bold=True,size=11,color="3E2600"),
       fill=fGold, align=aC)
    ws.row_dimensions[3].height = 22

    mg(ws,4,1,4,LAST_COL,
       val=f"{WOREDA}, {ZONE}, {REGION}, Ethiopia  ·  {AY_EC} EC / {AY_GC} GC",
       font=Font(name="Calibri",size=9,color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[4].height = 16

    # ── Row 5-6: student info (two halves) ────────────────────
    def info_row(row, left_lbl, left_col, right_lbl, right_col):
        ws.row_dimensions[row].height = 17
        # left label
        mg(ws,row,1,row,3, val=left_lbl,
           font=Font(name="Calibri",bold=True,size=9,color=WH),
           fill=fSub, align=aL)
        # left value
        mg(ws,row,4,row,7,
           font=fB, fill=fIn, align=aL)
        # right label
        mg(ws,row,8,row,10, val=right_lbl,
           font=Font(name="Calibri",bold=True,size=9,color=WH),
           fill=fSub, align=aL)
        # right value
        mg(ws,row,11,row,LAST_COL,
           font=fB, fill=fIn, align=aL)

    info_fields = [
        ("Maqaa Barataa / Student Name","Kutaa / Grade & Section"),
        ("Lak. ID / Student ID","Saala / Gender"),
        ("Bara Dhalootaa / Date of Birth","Umurii / Age"),
        ("Maqaa Abbaa / Father's Name","Bilbila / Phone"),
        ("Maqaa Haadhaa / Mother's Name","Aanaa / District"),
        ("Ganda / Kebele","Guyyaa Gabassaa / Report Date"),
    ]
    for i,(lft,rgt) in enumerate(info_fields):
        info_row(5+i, lft, 1, rgt, 8)

    # Photo placeholder (rows 5-10, cols 1 block on right side)
    # Actually embed photo box as a note in last column – skip for simplicity

    # ── Row 11: spacer ───────────────────────────────────────
    ws.row_dimensions[11].height = 6

    # ── Row 12-14: Academic table headers ─────────────────────
    mg(ws,12,1,12,LAST_COL,
       val="ADEEMSA BARNOOTAA / ACADEMIC PERFORMANCE",
       font=Font(name="Calibri",bold=True,size=11,color=WH),
       fill=fHdr, align=aC)
    ws.row_dimensions[12].height = 22

    # Row 13: Semester group headers
    mg(ws,13,COL_SUBJ,13,COL_SUBJ, val="Barnoota\nSubject",
       font=fH, fill=fHdr, align=aC)
    mg(ws,13,COL_S1_0,13,COL_S1_TOT,
       val="TERM I / SEMESTER I",
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fSub, align=aC)
    mg(ws,13,COL_S2_0,13,COL_S2_TOT,
       val="TERM II / SEMESTER II",
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fSub, align=aC)
    mg(ws,13,COL_RMK,13,COL_RMK, val="Yaada\nRemark",
       font=fH, fill=fHdr, align=aC)
    ws.row_dimensions[13].height = 22

    # Row 14: Part labels
    sc(ws,14,COL_SUBJ,"",font=fH, fill=fHdr, align=aC, border=bT)
    for i,lbl in enumerate(PART_LABELS):
        for sem_off in (0, N_PARTS):
            col = COL_S1_0 + sem_off + i
            c = ws.cell(row=14, column=col)
            c.value = lbl
            c.font  = Font(name="Calibri",bold=True,size=8,color=WH)
            c.fill  = fHdr
            c.alignment = aRot
            c.border    = bT
    sc(ws,14,COL_RMK,"",font=fH,fill=fHdr,align=aC,border=bT)
    ws.row_dimensions[14].height = 58

    # ── Rows 15+: Subject score rows ─────────────────────────
    DATA_SUBJ_START = 15
    for si, subj in enumerate(subjects):
        row = DATA_SUBJ_START + si
        ws.row_dimensions[row].height = 17
        alt = (si % 2 == 1)
        rf  = fAlt if alt else fWht

        # Subject label
        c = ws.cell(row=row, column=COL_SUBJ)
        c.value = f"{subj[0]}  /  {subj[2]}"
        c.font  = Font(name="Calibri", bold=True, size=9)
        c.fill  = rf; c.alignment = aL; c.border = bT

        # Sem I and II input + total
        for sem in (0,1):
            s0 = COL_S1_0 + sem * N_PARTS
            # Parts 1-5: input cells (not total)
            for p in range(5):
                c = ws.cell(row=row, column=s0+p)
                c.fill = fIn; c.alignment = aC; c.border = bT
                c.number_format = "0"
            # Total (part 6): formula =SUM(parts 1-5)
            p1 = cl(s0); p5 = cl(s0+4); tot_c = cl(s0+5)
            c = ws.cell(row=row, column=s0+5)
            c.value = f"=IF(COUNTA({p1}{row}:{p5}{row})=0,\"\",SUM({p1}{row}:{p5}{row}))"
            c.font  = fSB
            c.fill  = fGoldL; c.alignment = aC; c.border = bT
            c.number_format = "0.0"

        # Remark
        c = ws.cell(row=row, column=COL_RMK)
        c.fill = rf; c.alignment = aL; c.border = bT

    n_subjs = len(subjects)
    AFTER_ROW = DATA_SUBJ_START + n_subjs   # first row after subjects

    # ── Summary section ───────────────────────────────────────
    ws.row_dimensions[AFTER_ROW].height = 8   # spacer

    SR = AFTER_ROW + 1     # summary block start

    # Header spanning all 3 panels
    mg(ws,SR,1,SR,LAST_COL,
       val="WALIGALA / ARGAMA / BEHAVIOUR · SUMMARY / ATTENDANCE / LIFE SKILLS",
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fHdr, align=aC)
    ws.row_dimensions[SR].height = 20

    SR += 1  # now on summary data row start

    # Panel column bounds
    P1_S = 1;  P1_E = 4    # WALIGALA/SUMMARY
    P2_S = 5;  P2_E = 9    # ARGAMA/ATTENDANCE
    P3_S = 10; P3_E = LAST_COL  # BEHAVIOUR

    # Panel sub-headers
    for (cs,ce,lbl) in [(P1_S,P1_E,"WALIGALA / SUMMARY"),
                        (P2_S,P2_E,"ARGAMA / ATTENDANCE"),
                        (P3_S,P3_E,"BEHAVIOUR & LIFE SKILLS")]:
        mg(ws,SR,cs,SR,ce, val=lbl,
           font=Font(name="Calibri",bold=True,size=9,color=WH),
           fill=fSub, align=aC)
    ws.row_dimensions[SR].height = 18
    SR += 1

    # Panel 1: summary rows
    sum_items = [
        ("Waligala Sem I / Total Term I",""),
        ("Waligala Sem II / Total Term II",""),
        ("Gidgala / Overall Average",""),
        ("Sadarkaa / Class Rank",""),
        ("Baay. Kutaa / Class Size",""),
        ("Bu'aa / Result",""),
    ]
    for row_off, (lbl, _) in enumerate(sum_items):
        row = SR + row_off
        ws.row_dimensions[row].height = 17
        mg(ws,row,P1_S,row,P1_S+1, val=lbl,
           font=Font(name="Calibri",bold=True,size=9),
           fill=fGoldL, align=aL)
        mg(ws,row,P1_S+2,row,P1_E,
           font=fB, fill=fIn, align=aC)

    # Panel 2: attendance rows
    att_items = ["Guyyaa Barumsaa / School Days",
                 "Argamuu / Present",
                 "Hin Argamne / Absent",
                 "Dheera / Late",
                 "% Argama / Attendance %"]
    for row_off, lbl in enumerate(att_items):
        row = SR + row_off
        ws.row_dimensions[row].height = 17
        mg(ws,row,P2_S,row,P2_S+2, val=lbl,
           font=Font(name="Calibri",bold=True,size=9),
           fill=fGoldL, align=aL)
        mg(ws,row,P2_S+3,row,P2_E,
           font=fB, fill=fIn, align=aC)

    # Panel 3: behaviour ratings
    RATINGS = ["Excellent","Very Good","Good","Fair","Needs Improve"]
    beh_items = ["Naamusa / Discipline","Kabajaa / Respect",
                 "Hirmaannaa / Participation","Hogganummaa / Leadership",
                 "Gamtaan hoj. / Teamwork","Itti gaaf. / Responsibility"]

    # Rating header row
    rat_row = SR
    for ri, rat in enumerate(RATINGS):
        ws.cell(row=rat_row, column=P3_S+1+ri).value = rat
        ws.cell(row=rat_row, column=P3_S+1+ri).font  = Font(name="Calibri",bold=True,size=7,color=WH)
        ws.cell(row=rat_row, column=P3_S+1+ri).fill  = fSub
        ws.cell(row=rat_row, column=P3_S+1+ri).alignment = Alignment(horizontal="center",vertical="center",wrap_text=True,text_rotation=90)
        ws.cell(row=rat_row, column=P3_S+1+ri).border = bT

    for row_off, lbl in enumerate(beh_items):
        row = SR + row_off
        ws.row_dimensions[row].height = 17
        ws.cell(row=row, column=P3_S).value = lbl
        ws.cell(row=row, column=P3_S).font  = Font(name="Calibri",bold=True,size=9)
        ws.cell(row=row, column=P3_S).fill  = fGoldL
        ws.cell(row=row, column=P3_S).alignment = aL
        ws.cell(row=row, column=P3_S).border = bT
        for ri in range(len(RATINGS)):
            c = ws.cell(row=row, column=P3_S+1+ri)
            c.value = "☐"   # ballot box ☐
            c.font  = Font(name="Calibri",size=10)
            c.fill  = fWht; c.alignment = aC; c.border = bT

    SR += max(len(sum_items), len(att_items), len(beh_items))

    # ── Promotion status row ───────────────────────────────────
    ws.row_dimensions[SR].height = 20
    mg(ws,SR,1,SR,4,
       val="Guddina / Promotion Status (Sem II):",
       font=Font(name="Calibri",bold=True,size=10),
       fill=fGoldL, align=aL)
    for i,(stat,col) in enumerate([("Darbe/Promoted",5),("Kufe/Detained",7),("Hin Darb./Not Promoted",9)]):
        ws.cell(row=SR,column=col).value = f"☐ {stat}"
        ws.cell(row=SR,column=col).font  = fBo
        ws.cell(row=SR,column=col).fill  = fWht
        ws.cell(row=SR,column=col).alignment = aL
        ws.cell(row=SR,column=col).border = bT
    SR += 1

    # ── Comments section ──────────────────────────────────────
    ws.row_dimensions[SR].height = 8
    SR += 1

    for lbl in [("Yaada Barsiisaa Daree / Homeroom Teacher Comment",1,7),
                ("Yaada Maatii / Parent / Guardian Comment",8,LAST_COL)]:
        mg(ws,SR,lbl[1],SR,lbl[2], val=lbl[0],
           font=Font(name="Calibri",bold=True,size=9,color=WH),
           fill=fSub, align=aL)
    ws.row_dimensions[SR].height = 18; SR += 1

    for row_off in range(3):
        row = SR + row_off
        ws.row_dimensions[row].height = 18
        for (cs,ce) in [(1,7),(8,LAST_COL)]:
            mg(ws,row,cs,row,ce,
               val="....................................................................",
               font=Font(name="Calibri",size=9,color="AAAAAA"),
               fill=fWht, align=aL)
    SR += 3

    # ── Signature row ─────────────────────────────────────────
    ws.row_dimensions[SR].height = 8; SR += 1

    mg(ws,SR,1,SR,LAST_COL,
       val="SAHIHHOO / SIGNATURES",
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fHdr, align=aC)
    ws.row_dimensions[SR].height = 18; SR += 1

    sigs = [
        ("Barsiisaa Daree\nHomeroom Teacher", 1, 4),
        ("Maatii / Guarantor\nParent / Guardian", 5, 9),
        ("Ga. Bulchaa\nSchool Director", 10, LAST_COL),
    ]
    for name, cs, ce in sigs:
        ws.row_dimensions[SR].height = 34
        mg(ws,SR,cs,SR,ce, val=name,
           font=Font(name="Calibri",bold=True,size=9),
           fill=fGoldL, align=aC)
    SR += 1

    for name, cs, ce in sigs:
        ws.row_dimensions[SR].height = 14
        mg(ws,SR,cs,SR,ce,
           val="________________________________",
           font=Font(name="Calibri",size=9,color="777777"),
           fill=fWht, align=aC)
    SR += 1

    for name, cs, ce in sigs:
        ws.row_dimensions[SR].height = 14
        mg(ws,SR,cs,SR,ce,
           val="Guyyaa / Date: _______________",
           font=Font(name="Calibri",size=9),
           fill=fWht, align=aC)
    SR += 1

    # ── Footer ────────────────────────────────────────────────
    ws.row_dimensions[SR].height = 8; SR += 1
    mg(ws,SR,1,SR,LAST_COL,
       val=f"⦿  {WOREDA}  •  {ZONE}  •  {REGION}  •  Ethiopia  ⦿",
       font=Font(name="Calibri",size=9,color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[SR].height = 16

    print_portrait(ws)
    ws.print_area = f"A1:{cl(LAST_COL)}{SR}"
    return ws


# ─────────────────────────────────────────────────────────────
# Inf0 SHEET
# ─────────────────────────────────────────────────────────────
def build_inf0(wb):
    ws = wb.create_sheet("Inf0")
    mg(ws,1,1,1,4,
       val="Inf0 · መረጃ · School Information",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 28

    fields = [
        ("School Name (English)",   SCHOOL_EN),
        ("School Name (Afan Oromo)",SCHOOL_OM),
        ("School Name (Amharic)",   SCHOOL_AM),
        ("Region",                  REGION),
        ("Zone",                    ZONE),
        ("Woreda",                  WOREDA),
        ("Principal",               PRINCIPAL),
        ("Academic Year (EC)",      AY_EC),
        ("Academic Year (GC)",      AY_GC),
        ("Pass Mark (%)",           PASS_MARK),
        ("Grades",                  "9 – 12"),
        ("Sections",                "A – K"),
    ]
    for i,(lbl,val) in enumerate(fields):
        row = i + 2
        ws.row_dimensions[row].height = 18
        mg(ws,row,1,row,2, val=lbl,
           font=fBo, fill=fSub if i%2==0 else fGray, align=aL)
        mg(ws,row,3,row,4, val=val,
           font=fB, fill=fIn, align=aL)

    for col,w in zip("ABCD",[28,28,28,28]):
        ws.column_dimensions[col].width = w
    return ws


# ─────────────────────────────────────────────────────────────
# OptionList SHEET
# ─────────────────────────────────────────────────────────────
def build_option_list(wb):
    ws = wb.create_sheet("OptionList")
    mg(ws,1,1,1,6,
       val="OptionList · Reference Lists for Dropdowns",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 26

    lists = {
        "Sex (Saala)":      ["M","F"],
        "Sections (Kutaa)": list("ABCDEFGHIJK"),
        "Grade (Kutaa)":    [9,10,11,12],
        "Stream (Damee)":   ["NS","SS","GP"],
        "Semester":         ["I","II"],
        "Status (Bu'aa)":   ["Darbe/Pass","Kufe/Fail","Incomplete","Bahe/Dropout"],
        "YesNo":            ["Eeyyee/Yes","Lakkii/No"],
    }

    col = 1
    for header, items in lists.items():
        ws.cell(row=2,column=col).value = header
        ws.cell(row=2,column=col).font  = fH
        ws.cell(row=2,column=col).fill  = fSub
        ws.cell(row=2,column=col).alignment = aC
        ws.cell(row=2,column=col).border    = bT
        ws.row_dimensions[2].height = 20
        for ri,item in enumerate(items):
            ws.cell(row=3+ri,column=col).value = item
            ws.cell(row=3+ri,column=col).font  = fB
            ws.cell(row=3+ri,column=col).fill  = fAlt if ri%2==0 else fWht
            ws.cell(row=3+ri,column=col).alignment = aC
            ws.cell(row=3+ri,column=col).border    = bT
        ws.column_dimensions[cl(col)].width = 18
        col += 1
    return ws


# ─────────────────────────────────────────────────────────────
# Subjectiwaan SHEET
# ─────────────────────────────────────────────────────────────
def build_subjectiwaan(wb):
    ws = wb.create_sheet("Subjectiwaan")
    mg(ws,1,1,1,5,
       val="Subjectiwaan · ምዘካም · Subject Master List",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 28

    hdrs = ["#","Afaan Oromoo","Amharic እንግሊዘኛ","English","Grades"]
    for ci,h in enumerate(hdrs,1):
        ws.cell(row=2,column=ci).value = h
        ws.cell(row=2,column=ci).font  = fH
        ws.cell(row=2,column=ci).fill  = fHdr
        ws.cell(row=2,column=ci).alignment = aC
        ws.cell(row=2,column=ci).border    = bT
    ws.row_dimensions[2].height = 20

    all_subjs = []
    for s9 in SUBJ_9_10:
        all_subjs.append(s9 + ("9–10",))
    for s11 in SUBJ_11_12:
        all_subjs.append(s11 + ("11–12",))

    for ri,row_data in enumerate(all_subjs):
        row = ri + 3
        ws.row_dimensions[row].height = 18
        alt = ri%2==0
        ws.cell(row=row,column=1).value = ri+1
        ws.cell(row=row,column=1).fill  = fAlt if alt else fWht
        ws.cell(row=row,column=1).alignment = aC
        ws.cell(row=row,column=1).border = bT
        for ci,val in enumerate(row_data,2):
            ws.cell(row=row,column=ci).value = val
            ws.cell(row=row,column=ci).font  = fB
            ws.cell(row=row,column=ci).fill  = fAlt if alt else fWht
            ws.cell(row=row,column=ci).alignment = aL
            ws.cell(row=row,column=ci).border    = bT

    for col,w in zip("ABCDE",[5,24,24,20,10]):
        ws.column_dimensions[cl(ord(col)-ord('A')+1)].width = w
    return ws


# ─────────────────────────────────────────────────────────────
# BAAFATA (Table of Contents)
# ─────────────────────────────────────────────────────────────
def build_baafata(wb):
    ws = wb.create_sheet("Baafata", 0)    # insert at position 0

    mg(ws,1,1,1,4,
       val=f"★  BAAFATA · የይዘት ይወቴቾች  ·  TABLE OF CONTENTS  ★",
       font=fT, fill=fHdr, align=aC)
    ws.row_dimensions[1].height = 32

    mg(ws,2,1,2,4,
       val=f"{SCHOOL_EN}  ·  {AY_EC} EC / {AY_GC} GC",
       font=Font(name="Calibri",bold=True,size=10,color=WH),
       fill=fSub, align=aC)
    ws.row_dimensions[2].height = 20

    # Group headers + sheet entries
    groups = [
        ("SETUP", [
            ("Inf0",         "School info, year, pass mark"),
            ("OptionList",   "Reference lists for dropdowns"),
            ("Subjectiwaan", "Subject master list"),
        ]),
        ("CLASS ROSTERS", [
            ("Ros9",  "Grade 9 – Class Roster (12 subjects)"),
            ("Ros10", "Grade 10 – Class Roster (12 subjects)"),
            ("Ros11", "Grade 11 – Class Roster (NS / SS, 10 subjects)"),
            ("Ros12", "Grade 12 – Class Roster (NS / SS, 10 subjects)"),
        ]),
        ("REPORT CARDS", [
            ("Kard9",  "Grade 9  – Student Report Card template"),
            ("Kard10", "Grade 10 – Student Report Card template"),
            ("Kard11", "Grade 11 – Student Report Card template"),
            ("Kard12", "Grade 12 – Student Report Card template"),
        ]),
    ]

    row = 4
    for grp_name, entries in groups:
        # Group header
        mg(ws,row,1,row,4,
           val=grp_name,
           font=Font(name="Calibri",bold=True,size=11,color=WH),
           fill=fGold if "ROSTER" in grp_name else fSub,
           align=aL)
        ws.row_dimensions[row].height = 22
        row += 1

        for sname, desc in entries:
            ws.row_dimensions[row].height = 18
            # Sheet name as hyperlink
            c = ws.cell(row=row, column=1)
            c.value     = sname
            c.font      = fLk
            c.fill      = fAlt if row%2==0 else fWht
            c.alignment = aL
            c.border    = bT
            try:
                c.hyperlink = f"#{sname}!A1"
            except Exception:
                pass

            # Description
            d = ws.cell(row=row, column=2)
            d.value = desc; d.font = fB
            d.fill  = fAlt if row%2==0 else fWht
            d.alignment = aL; d.border = bT

            # Back link
            b = ws.cell(row=row, column=3)
            b.value = "← Baafata"; b.font = fLk
            b.fill  = fAlt if row%2==0 else fWht
            b.alignment = aC; b.border = bT
            try:
                b.hyperlink = "#Baafata!A1"
            except Exception:
                pass

            row += 1
        row += 1  # spacer

    # Column widths
    ws.column_dimensions["A"].width = 18
    ws.column_dimensions["B"].width = 50
    ws.column_dimensions["C"].width = 16
    ws.column_dimensions["D"].width = 16
    return ws


# ─────────────────────────────────────────────────────────────
# MAIN
# ─────────────────────────────────────────────────────────────
def main():
    wb = openpyxl.Workbook()
    wb.remove(wb.active)      # remove blank default sheet

    # Build content sheets first
    build_inf0(wb)
    build_option_list(wb)
    build_subjectiwaan(wb)

    for g in (9, 10, 11, 12):
        build_roster(wb, g)

    for g in (9, 10, 11, 12):
        build_kard(wb, g)

    # TOC last (so all sheet names exist for hyperlinks) – inserted at pos 0
    build_baafata(wb)

    # Tab colours
    tab_cols = {
        "Baafata":       "1A5E20",
        "Inf0":          "2E7D32",
        "OptionList":    "388E3C",
        "Subjectiwaan":  "43A047",
        "Ros9":          "F57F17",
        "Ros10":         "F9A825",
        "Ros11":         "FF8F00",
        "Ros12":         "FF6F00",
        "Kard9":         "1565C0",
        "Kard10":        "1976D2",
        "Kard11":        "1E88E5",
        "Kard12":        "2196F3",
    }
    for sname, colour in tab_cols.items():
        if sname in wb.sheetnames:
            wb[sname].sheet_properties.tabColor = colour

    out = "SJASS_Records_2018EC.xlsx"
    wb.save(out)
    print(f"[OK] Saved: {out}")
    print(f"     Sheets: {wb.sheetnames}")


if __name__ == "__main__":
    main()
