import datetime
import math
from typing import Optional, Dict, Any, List
from db import query, query_one, execute, execute_last_id

def list_snowball_debts(status: str = "active", team_id: Optional[int] = None, limit: int = 50) -> Dict[str, Any]:
    """List all debts and liabilities for the current team or company, including balance, APR, minimum payments, and payoff status."""
    target_team = team_id or 1
    
    where_clauses = ["team_id = %s"]
    params = [target_team]
    
    if status == "active":
        where_clauses.append("is_paid = 0")
    elif status == "paid":
        where_clauses.append("is_paid = 1")
        
    sql = f"SELECT * FROM snowball_debts WHERE {' AND '.join(where_clauses)} ORDER BY current_balance ASC LIMIT %s"
    params.append(limit)
    
    debts = query(sql, tuple(params))
    
    formatted = []
    for d in debts:
        orig = float(d['original_balance'] or d['current_balance'] or 0)
        curr = float(d['current_balance'] or 0)
        rate = float(d['interest_rate'] or 0)
        min_p = float(d['minimum_payment'] or 0)
        paid = orig - curr if orig >= curr else 0.0
        pct = round((paid / orig * 100), 1) if orig > 0 else 100.0
        monthly_int = (curr * (rate / 100)) / 12.0
        
        formatted.append({
            "id": d['id'],
            "name": d['name'],
            "creditor": d['creditor'],
            "original_balance": orig,
            "current_balance": curr,
            "interest_rate": rate,
            "minimum_payment": min_p,
            "monthly_interest_estimate": round(monthly_int, 2),
            "progress_percent": pct,
            "due_day": d['due_day'],
            "category": d['category'],
            "is_paid": bool(d['is_paid']),
            "paid_at": str(d['paid_at']) if d.get('paid_at') else None,
            "notes": d['notes'],
        })
        
    return {
        "team_id": target_team,
        "total_debts": len(formatted),
        "debts": formatted
    }

def create_or_update_snowball_debt(
    name: str,
    current_balance: float,
    minimum_payment: float,
    creditor: Optional[str] = None,
    original_balance: Optional[float] = None,
    interest_rate: float = 0.0,
    due_day: Optional[int] = None,
    category: Optional[str] = None,
    notes: Optional[str] = None,
    debt_id: Optional[int] = None,
    team_id: Optional[int] = None
) -> Dict[str, Any]:
    """Create a new debt entry or update existing balance, APR, minimum payment, or creditor."""
    target_team = team_id or 1
    orig_bal = original_balance if original_balance is not None else current_balance
    
    if debt_id:
        existing = query_one("SELECT * FROM snowball_debts WHERE id = %s AND team_id = %s", (debt_id, target_team))
        if not existing:
            return {"error": f"Debt #{debt_id} not found."}
            
        execute("""
            UPDATE snowball_debts
            SET name = %s, creditor = %s, original_balance = %s, current_balance = %s,
                interest_rate = %s, minimum_payment = %s, due_day = %s, category = %s, notes = %s,
                updated_at = NOW()
            WHERE id = %s AND team_id = %s
        """, (name, creditor, orig_bal, current_balance, interest_rate, minimum_payment, due_day, category, notes, debt_id, target_team))
        
        return {
            "status": "debt_updated",
            "debt_id": debt_id,
            "name": name,
            "current_balance": current_balance,
            "minimum_payment": minimum_payment,
            "module_route": "/app/snowball/debts"
        }
    else:
        new_id = execute_last_id("""
            INSERT INTO snowball_debts (team_id, name, creditor, original_balance, current_balance, interest_rate, minimum_payment, due_day, category, notes, is_paid, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 0, NOW(), NOW())
        """, (target_team, name, creditor, orig_bal, current_balance, interest_rate, minimum_payment, due_day, category, notes))
        
        return {
            "status": "debt_created",
            "debt_id": new_id,
            "name": name,
            "current_balance": current_balance,
            "minimum_payment": minimum_payment,
            "module_route": "/app/snowball/debts"
        }

def log_snowball_payment(debt_id: int, amount: float, payment_date: Optional[str] = None, note: Optional[str] = None) -> Dict[str, Any]:
    """Log an extra or regular debt payment towards a specific debt, automatically updating balance and payoff status."""
    debt = query_one("SELECT * FROM snowball_debts WHERE id = %s", (debt_id,))
    if not debt:
        return {"error": f"Debt #{debt_id} not found."}
        
    if amount <= 0:
        return {"error": "Payment amount must be greater than 0."}
        
    p_date = payment_date or datetime.date.today().isoformat()
    team_id = debt['team_id']
    
    pay_id = execute_last_id("""
        INSERT INTO snowball_payments (team_id, debt_id, amount, payment_date, note, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, NOW(), NOW())
    """, (team_id, debt_id, amount, p_date, note or "Logged via MCP Tool"))
    
    curr = float(debt['current_balance'] or 0)
    new_balance = max(0.0, curr - amount)
    is_paid = 1 if new_balance <= 0.0001 else 0
    paid_at = datetime.datetime.now() if is_paid else None
    
    execute("""
        UPDATE snowball_debts
        SET current_balance = %s, is_paid = %s, paid_at = %s, updated_at = NOW()
        WHERE id = %s
    """, (new_balance, is_paid, paid_at, debt_id))
    
    return {
        "status": "payment_recorded",
        "payment_id": pay_id,
        "debt_id": debt_id,
        "debt_name": debt['name'],
        "payment_amount": amount,
        "remaining_balance": round(new_balance, 2),
        "is_paid_off": bool(is_paid),
        "module_route": "/app/snowball/payments"
    }

def calculate_snowball_payoff_plan(strategy: str = "snowball", extra_monthly_budget: float = 0.0, team_id: Optional[int] = None) -> Dict[str, Any]:
    """Simulate and compare Debt Snowball vs Debt Avalanche payoff schedules with amortization and interest savings."""
    target_team = team_id or 1
    debts = query("SELECT * FROM snowball_debts WHERE team_id = %s AND is_paid = 0", (target_team,))
    
    if not debts:
        return {
            "status": "no_debts",
            "message": "No active debts found. Company is completely debt-free!",
            "total_balance": 0.0
        }
        
    def simulate(debt_list: List[Dict[str, Any]], strat: str, extra: float):
        sim_debts = []
        for d in debt_list:
            sim_debts.append({
                "id": d['id'],
                "name": d['name'],
                "balance": float(d['current_balance']),
                "rate": float(d['interest_rate']),
                "min_payment": float(d['minimum_payment']),
            })
            
        total_interest = 0.0
        total_paid = 0.0
        month = 0
        rollover_surplus = 0.0
        schedule = []
        
        while any(d['balance'] > 0.01 for d in sim_debts) and month < 360:
            month += 1
            active = [d for d in sim_debts if d['balance'] > 0.01]
            if strat == "avalanche":
                active.sort(key=lambda x: x['rate'], reverse=True)
            else:
                active.sort(key=lambda x: x['balance'], reverse=False)
                
            available_extra = extra + rollover_surplus
            current_month_rollover_addition = 0.0
            
            for d in active:
                interest = (d['balance'] * (d['rate'] / 100.0)) / 12.0
                total_interest += interest
                d['balance'] += interest
                
                payment = min(d['balance'], d['min_payment'])
                
                if d['id'] == active[0]['id']:
                    extra_applied = min(d['balance'] - payment, available_extra)
                    payment += extra_applied
                    available_extra -= extra_applied
                    
                d['balance'] -= payment
                total_paid += payment
                
                if d['balance'] <= 0.01:
                    d['balance'] = 0.0
                    current_month_rollover_addition += d['min_payment']
                    schedule.append({"month": month, "debt_id": d['id'], "debt_name": d['name'], "status": "PAID_OFF"})
                    
            rollover_surplus += current_month_rollover_addition
            
        return {
            "total_months": month,
            "total_interest": round(total_interest, 2),
            "total_paid": round(total_paid, 2),
            "schedule": schedule
        }
        
    snowball_res = simulate(debts, "snowball", extra_monthly_budget)
    avalanche_res = simulate(debts, "avalanche", extra_monthly_budget)
    
    interest_saved = round(snowball_res['total_interest'] - avalanche_res['total_interest'], 2)
    months_saved = snowball_res['total_months'] - avalanche_res['total_months']
    
    chosen = avalanche_res if strategy == "avalanche" else snowball_res
    
    return {
        "team_id": target_team,
        "chosen_strategy": strategy,
        "extra_monthly_budget": extra_monthly_budget,
        "total_starting_debt": sum(float(d['current_balance']) for d in debts),
        "total_monthly_minimum": sum(float(d['minimum_payment']) for d in debts),
        "summary": {
            "total_months": chosen['total_months'],
            "total_interest_paid": chosen['total_interest'],
            "total_amount_paid": chosen['total_paid']
        },
        "strategy_comparison": {
            "snowball": {"months": snowball_res['total_months'], "interest_paid": snowball_res['total_interest']},
            "avalanche": {"months": avalanche_res['total_months'], "interest_paid": avalanche_res['total_interest']},
            "interest_saved_with_avalanche": max(0.0, interest_saved),
            "months_saved_with_avalanche": max(0, months_saved)
        },
        "payoff_milestones": chosen['schedule'],
        "module_route": "/app/snowball/plan"
    }

def get_snowball_financial_summary(team_id: Optional[int] = None) -> Dict[str, Any]:
    """Retrieve high-level debt payoff KPIs, total liability, monthly debt service, estimated debt-free date, total interest savings, and cashflow surplus."""
    target_team = team_id or 1
    debts = query("SELECT * FROM snowball_debts WHERE team_id = %s", (target_team,))
    
    active_debts = [d for d in debts if not d['is_paid']]
    paid_debts = [d for d in debts if d['is_paid']]
    
    total_original = sum(float(d['original_balance'] or d['current_balance'] or 0) for d in debts)
    total_current = sum(float(d['current_balance'] or 0) for d in active_debts)
    total_min = sum(float(d['minimum_payment'] or 0) for d in active_debts)
    monthly_int = sum((float(d['current_balance'] or 0) * (float(d['interest_rate'] or 0) / 100)) / 12 for d in active_debts)
    
    cashflows = query("SELECT * FROM snowball_cashflows WHERE team_id = %s", (target_team,))
    
    def norm_cf(c):
        amt = float(c['amount'] or 0)
        freq = c.get('frequency', 'monthly')
        if freq == 'yearly': return amt / 12.0
        elif freq == 'weekly': return amt * 4.333
        elif freq == 'bi-weekly': return (amt * 26.0) / 12.0
        return amt
        
    monthly_income = sum(norm_cf(c) for c in cashflows if c.get('type') == 'income')
    monthly_expenses = sum(norm_cf(c) for c in cashflows if c.get('type') == 'expense')
    surplus = max(0.0, monthly_income - monthly_expenses - total_min)
    
    setting = query_one("SELECT * FROM snowball_settings WHERE team_id = %s", (target_team,))
    extra_budget = float(setting['monthly_extra_budget']) if setting and setting.get('monthly_extra_budget') else 0.0
    strategy = setting.get('strategy', 'snowball') if setting else 'snowball'
    
    plan_info = calculate_snowball_payoff_plan(strategy=strategy, extra_monthly_budget=extra_budget, team_id=target_team) if active_debts else None
    
    return {
        "team_id": target_team,
        "kpis": {
            "total_current_debt_eur": round(total_current, 2),
            "total_original_debt_eur": round(total_original, 2),
            "progress_percent": round(((total_original - total_current) / total_original * 100), 1) if total_original > 0 else 100.0,
            "monthly_debt_service_eur": round(total_min, 2),
            "estimated_monthly_interest_eur": round(monthly_int, 2),
            "active_debts_count": len(active_debts),
            "paid_debts_count": len(paid_debts)
        },
        "payoff_outlook": {
            "strategy": strategy,
            "extra_monthly_budget": extra_budget,
            "months_to_debt_free": plan_info['summary']['total_months'] if plan_info and 'summary' in plan_info else 0,
            "projected_interest_to_pay": plan_info['summary']['total_interest_paid'] if plan_info and 'summary' in plan_info else 0.0
        },
        "cashflow_surplus_analysis": {
            "monthly_income_eur": round(monthly_income, 2),
            "monthly_expenses_eur": round(monthly_expenses, 2),
            "unallocated_surplus_eur": round(surplus, 2)
        },
        "module_route": "/app/snowball"
    }
