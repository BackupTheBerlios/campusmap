package CampusMap;

public class FMath {
	
	//calculates the current accelerated value for the current animation (calculates the accelerated interpolation value) 
	public static float calcAcceleration(float timePassedOfTotal, int accelIdent) {
		switch (accelIdent) {
			case 1: //acceleration at start and end
				return 1 - (2*(timePassedOfTotal*timePassedOfTotal*timePassedOfTotal) - 3*(timePassedOfTotal*timePassedOfTotal) + 1);
			case 2: //full speed at the end
				return (timePassedOfTotal*timePassedOfTotal);
			case 3: //full speed at start
				//return (1.0f-timePassedOfTotal)*(1.0f-timePassedOfTotal);
				return (float)Math.sqrt(timePassedOfTotal);
			case 0: //no acceleration
			default:
				return timePassedOfTotal;
		}	
		//return (1/(atan(4)*2)) * atan(timePassedOfTotal * 8 - 4) + 0.5;
	}
	
//	 Find a point ptt distanced by param t (in parametric space) from pt0 on the line (pt0,pt1) 
	private static void tween( Point pt0, Point pt1, double t, Point ptt ){
	        ptt.x = pt0.x + t * ( pt1.x - pt0.x );
	        ptt.y = pt0.y + t * ( pt1.y - pt0.y );
	}
//	 Evaluate Bezier
	public static float[] bezier( FVector b0, FVector b1, FVector b2, FVector b3, float t) 
	{
	        Point pR1 = new Point();
	        Point pE = new Point();
	        Point p21 = new Point();
	        Point p10 = new Point();
	        Point p32 = new Point();
	        Point pR0 = new Point();
	        
	        tween( new Point(b1), new Point(b2), t, p21 );
	        tween( new Point(b0), new Point(b1), t, p10 );
	        tween( new Point(b2), new Point(b3), t, p32 );
	        tween( p10, p21, t, pR1 );
	        tween( p21, p32, t, pE );
	        tween( pR1, pE, t, pR0 );
	        
	        return new float[]{(float)pR0.x, (float)pR0.y};
	}
	
}

class Point{
	double x;
	double y;
	public Point(){
		this.x=0;
		this.y=0;
	}
	public Point(double x,double y){
		this.x=x;
		this.y=y;
	} 
	public Point(FVector vectorIn){
		this.x=vectorIn.getX();
		this.y=vectorIn.getY();
	} 
}