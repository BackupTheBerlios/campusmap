package CampusMap;

//class that stores position and radius of a sphere
//divided into world- and modelspace to work well with 3Dmodels
//provides methods to test this sphere with other spheres, rays and planes for intersection
//also provides methods to scale and move the sphere in worldspace/modelspace

public class CollisionSphere {

	private FVector m_vPositionModelSpace;
	private	FVector m_vPositionWorldSpace;
        private FVector m_vCompundSpace;
        private FVector	lineToSphere;
        private float	s, t, q, l2, r2, m2, len; // vars for testray

	private	float	m_fRadius;

	public CollisionSphere(FVector vPosition, float fRadius) {
		m_vPositionModelSpace	= new FVector(vPosition);
		m_vPositionWorldSpace	= new FVector();
                calcCompundSpace();
		m_fRadius				= fRadius;
	}

	public CollisionSphere(float fXPos, float fYPos, float fZPos, float fRadius) {
		m_vPositionModelSpace	= new FVector(fXPos, fYPos, fZPos);
		m_vPositionWorldSpace	= new FVector();
                calcCompundSpace();
		m_fRadius				= fRadius;
	}

        private void calcCompundSpace(){
          m_vCompundSpace	= FVector.add(m_vPositionWorldSpace, m_vPositionModelSpace);
        }

	public float getRadius() {
		return m_fRadius;
	}

	public void setRadius(float newRadius) {
		m_fRadius = newRadius;
	}

	public FVector getPosition() {
          return m_vCompundSpace;
	}

	public void moveAbout(FVector about) {
          m_vPositionWorldSpace.addMe(about);
          calcCompundSpace();
	}

	public void moveTo(FVector to) {
          m_vPositionWorldSpace.set(to);
          calcCompundSpace();
	}

	public void scaleAbout(FVector scaleValue) {
          m_fRadius *= scaleValue.magnitude();
          m_vPositionModelSpace.x *= scaleValue.x;
          m_vPositionModelSpace.y *= scaleValue.y;
          m_vPositionModelSpace.z *= scaleValue.z;
          calcCompundSpace();
	}

	public void moveModelSpaceTo(FVector to) {
          m_vPositionModelSpace.set(to);
          calcCompundSpace();
	}

	public void moveModelSpaceAbout(FVector about) {
            FVector.add(m_vPositionModelSpace, about);
            calcCompundSpace();
	}

	public void rotateModelSpaceXYZ(FVector rotation) {
		m_vPositionModelSpace.rotateMeXYZ(rotation);
                calcCompundSpace();
	}

	public boolean testRay(FVector rayStart, FVector rayDir)
	{
		rayDir.normalizeMe();

                //System.out.println("Spaces:"+m_vPositionModelSpace+", "+m_vPositionWorldSpace);
		lineToSphere = FVector.subtract(getPosition(), rayStart);

		s  = FVector.dotProduct(lineToSphere, rayDir);
		l2 = lineToSphere.magnitudeSqr();
		r2 = m_fRadius * m_fRadius;

		if(l2 <= r2)
		{
			// Ray starts inside or on the sphere
			return true;
		}

		if(s < 0.0f)
		{
			// Ray heading in wrong direction
			return false;
		}

		m2 = l2 - (s*s);
		if(m2 > r2)
		{
			// Ray will never hit the sphere
			return false;
		}

		q = (float)(Math.sqrt(r2 - m2));
		t = s - q;

		len = (float)(Math.sqrt(l2));
		if(t > len)
		{
			// Ray hits beyond the far point of the line
			return false;
		}

		return true;
	}

	public boolean testPoint(FVector point)
	{
		FVector lineToSphere = FVector.subtract(getPosition(), point);
		if (lineToSphere.magnitudeSqr() > m_fRadius*m_fRadius)
			return true;
		return false;
	}

	public CollisionSphere testPoint(FVector point, float distance)
	{
/*		if (FVector.subtract(getPosition(), point).magnitudeSqr() < (m_fRadius+distance)*(m_fRadius+distance))
			return this;
*/		return null;
	}

	public FVector getTangent(FVector point) {
		FVector lineToSphere = FVector.subtract(getPosition(), point).normalize();
		return FVector.crossProduct(lineToSphere, new FVector(0,0,1));
	}

	public void debugDraw(CampusMap applet) {
		applet.fill(255, 100, 100, 255);
		finalDebugDraw(applet);
	}

	public void debugDraw(CampusMap applet, int color) {
		applet.fill(color);
		finalDebugDraw(applet);
	}

	private void finalDebugDraw(CampusMap applet) {
		FVector pos = getPosition();
		applet.pushMatrix();
		applet.translate(pos.getX(), pos.getY(), pos.getZ());
		applet.sphere(m_fRadius);
		applet.popMatrix();
	}

}
